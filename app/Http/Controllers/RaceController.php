<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceLineup;
use App\Models\RaceResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaceController extends Controller
{
    // Lista tutte le gare
    public function index()
    {
        $team = Auth::user()->playerTeam;

        $upcomingRaces = Race::where('status', 'lineup_open')
                             ->orWhere('status', 'upcoming')
                             ->orderBy('date')
                             ->get();

        $completedRaces = Race::where('status', 'completed')
                              ->orderBy('date', 'desc')
                              ->take(10)
                              ->get();

        return view('races.index', [
            'upcomingRaces' => $upcomingRaces,
            'completedRaces' => $completedRaces,
            'team' => $team,
        ]);
    }

    // Mostra dettaglio gara
    public function show(Race $race)
    {
        $team = Auth::user()->playerTeam;
        $lineup = $race->getLineupForTeam($team);

        $results = $race->results()
                        ->with('rider.category')
                        ->orderBy('position')
                        ->get();

        // Calcola crediti guadagnati dalla squadra
        $teamCredits = 0;
        if ($lineup && $race->hasResults()) {
            $teamCredits = $lineup->calculateCreditsEarned();
        }

        return view('races.show', [
            'race' => $race,
            'team' => $team,
            'lineup' => $lineup,
            'results' => $results,
            'teamCredits' => $teamCredits,
        ]);
    }

    // Form per schierare la formazione
    public function lineup(Race $race)
    {
        $team = Auth::user()->playerTeam;

        if (!$race->canSubmitLineup()) {
            return redirect()->route('races.show', $race)
                           ->with('error', 'Le formazioni per questa gara sono chiuse.');
        }

        $lineup = $race->getLineupForTeam($team);
        $selectedRiderIds = $lineup ? $lineup->riders->pluck('id')->toArray() : [];

        $availableRiders = $team->riders()->with('category')->get();

        return view('races.lineup', [
            'race' => $race,
            'team' => $team,
            'availableRiders' => $availableRiders,
            'selectedRiderIds' => $selectedRiderIds,
            'maxRiders' => $race->lineup_size,
        ]);
    }

    // Salva la formazione
    public function saveLineup(Request $request, Race $race)
    {
        $team = Auth::user()->playerTeam;

        if (!$race->canSubmitLineup()) {
            return redirect()->route('races.show', $race)
                           ->with('error', 'Le formazioni per questa gara sono chiuse.');
        }

        $request->validate([
            'riders' => 'required|array|min:1|max:' . $race->lineup_size,
            'riders.*' => 'exists:riders,id',
        ]);

        // Verifica che i corridori appartengano alla squadra
        $riderIds = $request->riders;
        $validRiders = $team->riders()->whereIn('id', $riderIds)->count();

        if ($validRiders !== count($riderIds)) {
            return back()->with('error', 'Alcuni corridori selezionati non appartengono alla tua squadra.');
        }

        DB::transaction(function () use ($race, $team, $riderIds) {
            // Trova o crea la formazione
            $lineup = RaceLineup::firstOrCreate([
                'race_id' => $race->id,
                'player_team_id' => $team->id,
            ]);

            // Sincronizza i corridori
            $lineup->riders()->sync($riderIds);
        });

        return redirect()->route('races.show', $race)
                        ->with('status', 'Formazione salvata con successo!');
    }

    // Classifica gara
    public function standings(Race $race)
    {
        if (!$race->hasResults()) {
            return redirect()->route('races.show', $race)
                           ->with('error', 'I risultati di questa gara non sono ancora disponibili.');
        }

        // Calcola i crediti per ogni squadra
        $teamStandings = [];

        $lineups = $race->lineups()->with(['playerTeam', 'riders'])->get();

        foreach ($lineups as $lineup) {
            $credits = $lineup->calculateCreditsEarned();
            $teamStandings[] = [
                'team' => $lineup->playerTeam,
                'credits' => $credits,
                'riders_count' => $lineup->riders->count(),
            ];
        }

        // Ordina per crediti
        usort($teamStandings, fn($a, $b) => $b['credits'] - $a['credits']);

        return view('races.standings', [
            'race' => $race,
            'teamStandings' => $teamStandings,
        ]);
    }
}
