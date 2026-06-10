<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PlayerTeam;
use App\Models\Trade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CreateTradeForm extends Component
{
    public Collection $otherTeams;
    public ?int $selectedTeamId = null;
    public ?Collection $myRoster = null;
    public ?Collection $selectedTeamRoster = null;

    public array $offeredRiderIds = [];
    public array $requestedRiderIds = [];
    public ?int $moneyOffer = null;    // Crediti che TU OFFRI (paghi)
    public ?int $moneyRequest = null;  // Crediti che TU CHIEDI (ricevi)

    public function mount()
    {
        $myTeamId = Auth::user()->playerTeam->id;
        $this->otherTeams = PlayerTeam::where('id', '!=', $myTeamId)->get();
        $this->myRoster = Auth::user()->playerTeam()->with('riders.category')->first()->riders;
    }

    public function updatedSelectedTeamId($teamId)
    {
        if ($teamId) {
            $this->selectedTeamRoster = PlayerTeam::with('riders.category')->find($teamId)->riders;
        } else {
            $this->selectedTeamRoster = null;
        }
        $this->reset(['offeredRiderIds', 'requestedRiderIds']);
    }

    public function submitTrade()
    {
        // Calcola money_adjustment dai due campi separati
        // moneyOffer (tu paghi) -> valore negativo
        // moneyRequest (tu ricevi) -> valore positivo
        $moneyOffer = $this->moneyOffer ?? 0;
        $moneyRequest = $this->moneyRequest ?? 0;

        // I crediti non possono essere negativi (un valore negativo invertirebbe chi paga)
        if ($moneyOffer < 0 || $moneyRequest < 0) {
            session()->flash('error', 'I crediti non possono essere negativi.');
            return;
        }

        // Non puoi compilare entrambi
        if ($moneyOffer > 0 && $moneyRequest > 0) {
            session()->flash('error', 'Non puoi sia offrire che chiedere crediti. Compila solo uno dei due campi.');
            return;
        }

        // Calcola il valore da salvare
        $moneyAdjustment = $moneyRequest - $moneyOffer;

        // Validazione: deve esserci ALMENO una delle tre cose
        if (empty($this->offeredRiderIds) &&
            empty($this->requestedRiderIds) &&
            $moneyAdjustment == 0) {
            session()->flash('error', 'Devi selezionare almeno un corridore da offrire/richiedere oppure specificare crediti.');
            return;
        }

        if (!$this->selectedTeamId) {
            session()->flash('error', 'Devi selezionare una squadra.');
            return;
        }

        $myTeam = Auth::user()->playerTeam;

        // Validazione: i corridori offerti devono appartenere alla mia squadra
        if (!empty($this->offeredRiderIds)) {
            $myRiderIds = $myTeam->riders()->pluck('id')->all();
            if (array_diff($this->offeredRiderIds, $myRiderIds)) {
                session()->flash('error', 'Puoi offrire solo corridori della tua squadra.');
                return;
            }
        }

        // Validazione: i corridori richiesti devono appartenere alla squadra selezionata
        if (!empty($this->requestedRiderIds)) {
            $theirRiderIds = PlayerTeam::find($this->selectedTeamId)->riders()->pluck('id')->all();
            if (array_diff($this->requestedRiderIds, $theirRiderIds)) {
                session()->flash('error', 'Puoi richiedere solo corridori della squadra selezionata.');
                return;
            }
        }

        // Validazione: verifica che l'utente abbia abbastanza budget se deve pagare
        if ($moneyOffer > 0) {
            if ($myTeam->balance < $moneyOffer) {
                session()->flash('error', "Non hai abbastanza budget! Il tuo saldo è {$myTeam->balance}M, ma vuoi offrire {$moneyOffer}M.");
                return;
            }
        }

        DB::transaction(function () use ($moneyAdjustment) {
            $trade = Trade::create([
                'offering_team_id' => Auth::user()->playerTeam->id,
                'receiving_team_id' => $this->selectedTeamId,
                'money_adjustment' => $moneyAdjustment,
                'status' => 'pending',
            ]);

            foreach ($this->offeredRiderIds as $riderId) {
                $trade->riders()->attach($riderId, ['direction' => 'offering']);
            }

            foreach ($this->requestedRiderIds as $riderId) {
                $trade->riders()->attach($riderId, ['direction' => 'receiving']);
            }
        });

        session()->flash('status', 'Proposta di scambio inviata con successo!');

        // Reset del form
        $this->reset(['offeredRiderIds', 'requestedRiderIds', 'moneyOffer', 'moneyRequest']);
        $this->selectedTeamId = null;
        $this->selectedTeamRoster = null;

        $this->dispatch('trade-proposed');
    }

    public function render()
    {
        return view('livewire.create-trade-form', [
            'myTeamRoster' => $this->myRoster,
            'selectedTeamRoster' => $this->selectedTeamRoster,
        ]);
    }
}