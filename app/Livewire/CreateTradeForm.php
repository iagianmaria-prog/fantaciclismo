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

        // Validazione: verifica che l'utente abbia abbastanza budget se deve pagare
        if ($moneyOffer > 0) {
            $myTeam = Auth::user()->playerTeam;

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