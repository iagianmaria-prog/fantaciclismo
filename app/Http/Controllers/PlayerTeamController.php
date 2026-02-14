<?php

namespace App\Http\Controllers;

use App\Models\PlayerTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Rider;
use App\Services\SettingManager;
use App\Models\Trade;
use App\Models\Race;
use App\Models\RaceLineup;
use App\Models\RaceResult;
use Exception;

class PlayerTeamController extends Controller
{
    /**
     * Mostra la pagina per creare una nuova squadra.
     */
    public function create()
    {
        return view('player-team.create');
    }

    /**
     * Salva la nuova squadra nel database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:player_teams'],
        ]);

        $initialBudget = SettingManager::get('initial_budget');

        PlayerTeam::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'balance' => $initialBudget,
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Mostra la pagina dell'asta con i corridori disponibili.
     */
    public function showAuction()
    {
        $riders = Rider::whereNull('player_team_id')->with('category')->get();
        return view('auction.show', ['riders' => $riders]);
    }

    /**
     * Logica per acquistare un corridore.
     */
    public function buyRider(Rider $rider)
    {
        DB::beginTransaction();
        try {
            $team = Auth::user()->playerTeam;

            if ($rider->player_team_id !== null) {
                return back()->with('error', 'Questo corridore è già stato acquistato!');
            }
            if ($team->balance < $rider->initial_value) {
                return back()->with('error', 'Budget non sufficiente per acquistare questo corridore!');
            }

            $categoryKey = 'max_' . strtolower($rider->category->name);
            $maxForCategory = SettingManager::get($categoryKey);

            if ($maxForCategory !== null) {
                $currentCountForCategory = $team->riders()->where('rider_category_id', $rider->rider_category_id)->count();
                if ($currentCountForCategory >= $maxForCategory) {
                    return back()->with('error', "Hai già raggiunto il numero massimo di corridori per la categoria {$rider->category->name}!");
                }
            }

            $team->balance -= $rider->initial_value;
            $team->save();
            $rider->player_team_id = $team->id;
            $rider->save();

            DB::commit();
            return back()->with('status', "Corridore {$rider->name} acquistato con successo!");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', "Si è verificato un errore imprevisto durante l'acquisto. Riprova.");
        }
    }

    /**
     * Logica per svincolare un corridore.
     */
    public function releaseRider(Rider $rider)
    {
        DB::beginTransaction();
        try {
            $team = Auth::user()->playerTeam;

            if ($rider->player_team_id !== $team->id) {
                return back()->with('error', 'Non puoi svincolare un corridore che non ti appartiene.');
            }

            $recoveryPercentage = SettingManager::get('release_recovery_percentage_mid_season');
            $recoveredValue = floor(($rider->initial_value * $recoveryPercentage) / 100);

            $team->balance += $recoveredValue;
            $team->save();
            $rider->player_team_id = null;
            $rider->save();

            DB::commit();
            return back()->with('status', "Corridore {$rider->name} svincolato! Hai recuperato {$recoveredValue} fantamilioni.");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', "Si è verificato un errore durante lo svincolo.");
        }
    }

    /**
     * Mostra la pagina del mercato scambi.
     */
    public function showMarket()
    {
        $myTeam = Auth::user()->playerTeam;
        if (!$myTeam) {
            return redirect()->route('player-team.create')->with('error', 'Devi prima creare una squadra per accedere al mercato.');
        }
        
        $myTeamId = $myTeam->id;

        $receivedTrades = Trade::where('receiving_team_id', $myTeamId)
                               ->where('status', 'pending')
                               ->with(['offeringTeam', 'offeredRiders', 'requestedRiders'])
                               ->get();

        $proposedTrades = Trade::where('offering_team_id', $myTeamId)
                               ->where('status', 'pending')
                               ->with(['receivingTeam', 'offeredRiders', 'requestedRiders'])
                               ->get();
        
        $otherTeams = PlayerTeam::where('id', '!=', $myTeamId)->get();

        return view('market.index', [
            'myTeam' => $myTeam,
            'receivedTrades' => $receivedTrades,
            'proposedTrades' => $proposedTrades,
            'otherTeams' => $otherTeams,
        ]);
    }

    /**
     * Accetta una proposta di scambio.
     */
    public function acceptTrade(Trade $trade)
    {
        DB::beginTransaction();
        try {
            $receivingTeam = Auth::user()->playerTeam;

            if ($trade->receiving_team_id !== $receivingTeam->id) {
                return back()->with('error', 'Non puoi accettare uno scambio non indirizzato a te.');
            }

            if ($trade->status !== 'pending') {
                return back()->with('error', 'Questo scambio non è più disponibile.');
            }

            $offeredRiders = $trade->riders()->wherePivot('direction', 'offering')->get();
            foreach ($offeredRiders as $rider) {
                if ($rider->player_team_id !== $trade->offering_team_id) {
                    return back()->with('error', "Il corridore {$rider->name} non è più disponibile per lo scambio.");
                }
            }

            $requestedRiders = $trade->riders()->wherePivot('direction', 'receiving')->get();
            foreach ($requestedRiders as $rider) {
                if ($rider->player_team_id !== $trade->receiving_team_id) {
                    return back()->with('error', "Il corridore {$rider->name} non è più nel tuo roster.");
                }
            }

            $offeringTeam = $trade->offeringTeam;
            
            foreach ($offeredRiders as $rider) {
                $categoryKey = 'max_' . strtolower($rider->category->name);
                $maxForCategory = SettingManager::get($categoryKey);
                
                if ($maxForCategory !== null) {
                    $currentCount = $receivingTeam->riders()
                        ->where('rider_category_id', $rider->rider_category_id)
                        ->count();
                    
                    $givingAwayCount = $requestedRiders->where('rider_category_id', $rider->rider_category_id)->count();
                    
                    if (($currentCount - $givingAwayCount + 1) > $maxForCategory) {
                        return back()->with('error', "Superato il limite massimo per la categoria {$rider->category->name}!");
                    }
                }
            }

            foreach ($offeredRiders as $rider) {
                $rider->player_team_id = $trade->receiving_team_id;
                $rider->save();
            }

            foreach ($requestedRiders as $rider) {
                $rider->player_team_id = $trade->offering_team_id;
                $rider->save();
            }

            if ($trade->money_adjustment != 0) {
                // money_adjustment > 0: chi propone RICEVE, chi accetta PAGA
                // money_adjustment < 0: chi propone PAGA, chi accetta RICEVE

                if ($trade->money_adjustment > 0) {
                    // Chi accetta deve pagare - verifico che abbia budget sufficiente
                    if ($receivingTeam->balance < $trade->money_adjustment) {
                        throw new Exception('Budget insufficiente per completare lo scambio. Devi pagare ' . $trade->money_adjustment . 'M ma hai solo ' . $receivingTeam->balance . 'M.');
                    }
                } else {
                    // Chi propone deve pagare - verifico che abbia budget sufficiente
                    $amountToPay = abs($trade->money_adjustment);
                    if ($offeringTeam->balance < $amountToPay) {
                        throw new Exception('La squadra proponente non ha più budget sufficiente per completare lo scambio.');
                    }
                }

                $offeringTeam->balance += $trade->money_adjustment;
                $receivingTeam->balance -= $trade->money_adjustment;

                $offeringTeam->save();
                $receivingTeam->save();
            }

            $trade->status = 'accepted';
            $trade->save();

            DB::commit();
            return back()->with('status', 'Scambio accettato con successo!');
            
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Errore durante l\'accettazione dello scambio: ' . $e->getMessage());
        }
    }

    /**
     * Rifiuta una proposta di scambio.
     */
    public function rejectTrade(Trade $trade)
    {
        try {
            $receivingTeam = Auth::user()->playerTeam;

            if ($trade->receiving_team_id !== $receivingTeam->id) {
                return back()->with('error', 'Non puoi rifiutare uno scambio non indirizzato a te.');
            }

            if ($trade->status !== 'pending') {
                return back()->with('error', 'Questo scambio è già stato processato.');
            }

            $trade->status = 'rejected';
            $trade->save();

            return back()->with('status', 'Scambio rifiutato.');
            
        } catch (Exception $e) {
            return back()->with('error', 'Errore durante il rifiuto: ' . $e->getMessage());
        }
    }

    /**
     * Cancella una proposta di scambio inviata.
     */
    public function cancelTrade(Trade $trade)
    {
        try {
            $offeringTeam = Auth::user()->playerTeam;

            if ($trade->offering_team_id !== $offeringTeam->id) {
                return back()->with('error', 'Non puoi cancellare uno scambio che non hai proposto.');
            }

            if ($trade->status !== 'pending') {
                return back()->with('error', 'Questo scambio non può più essere cancellato.');
            }

            $trade->status = 'cancelled';
            $trade->save();

            return back()->with('status', 'Proposta di scambio cancellata.');
            
        } catch (Exception $e) {
            return back()->with('error', 'Errore durante la cancellazione: ' . $e->getMessage());
        }
    }

    /**
     * Mostra lo storico di tutti gli scambi della squadra
     */
    public function showHistory()
    {
        $myTeam = Auth::user()->playerTeam;
        
        $trades = Trade::where(function($query) use ($myTeam) {
                $query->where('offering_team_id', $myTeam->id)
                      ->orWhere('receiving_team_id', $myTeam->id);
            })
            ->where('status', '!=', 'pending')
            ->with(['offeringTeam', 'receivingTeam'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('market.history', [
            'trades' => $trades,
            'myTeam' => $myTeam,
        ]);
    }

    /**
     * Mostra le statistiche complete della squadra
     */
    public function showStatistics()
    {
        $myTeam = Auth::user()->playerTeam;
        
        $riders = $myTeam->riders()->with('category')->get();
        $totalRiders = $riders->count();
        $totalValue = $riders->sum('initial_value');
        $averageValue = $totalRiders > 0 ? round($totalValue / $totalRiders, 2) : 0;
        
        $ridersByCategory = $riders->groupBy('category.name')->map(function($group) {
            return [
                'count' => $group->count(),
                'total_value' => $group->sum('initial_value'),
            ];
        });
        
        $allTrades = Trade::where(function($query) use ($myTeam) {
                $query->where('offering_team_id', $myTeam->id)
                      ->orWhere('receiving_team_id', $myTeam->id);
            })
            ->get();
        
        $tradesAccepted = $allTrades->where('status', 'accepted')->count();
        $tradesRejected = $allTrades->where('status', 'rejected')->count();
        $tradesCancelled = $allTrades->where('status', 'cancelled')->count();
        $tradesPending = $allTrades->where('status', 'pending')->count();
        
        $creditsReceived = Trade::where('offering_team_id', $myTeam->id)
            ->where('status', 'accepted')
            ->sum('money_adjustment');
        
        $creditsPaid = Trade::where('receiving_team_id', $myTeam->id)
            ->where('status', 'accepted')
            ->sum('money_adjustment');
        
        $creditsBalance = $creditsReceived - $creditsPaid;
        
        $initialBudget = SettingManager::get('initial_budget');
        $currentBudget = $myTeam->balance;
        $totalSpent = $initialBudget - $currentBudget + $creditsBalance;
        
        $allTeams = PlayerTeam::with('riders')->get()->map(function($team) {
            return [
                'id' => $team->id,
                'name' => $team->name,
                'total_value' => $team->riders->sum('initial_value'),
                'riders_count' => $team->riders->count(),
                'balance' => $team->balance,
            ];
        })->sortByDesc('total_value')->values();
        
        $myPosition = $allTeams->search(function($team) use ($myTeam) {
            return $team['id'] === $myTeam->id;
        }) + 1;
        
        return view('statistics.index', [
            'myTeam' => $myTeam,
            'totalRiders' => $totalRiders,
            'totalValue' => $totalValue,
            'averageValue' => $averageValue,
            'ridersByCategory' => $ridersByCategory,
            'tradesAccepted' => $tradesAccepted,
            'tradesRejected' => $tradesRejected,
            'tradesCancelled' => $tradesCancelled,
            'tradesPending' => $tradesPending,
            'creditsReceived' => $creditsReceived,
            'creditsPaid' => $creditsPaid,
            'creditsBalance' => $creditsBalance,
            'initialBudget' => $initialBudget,
            'currentBudget' => $currentBudget,
            'totalSpent' => $totalSpent,
            'allTeams' => $allTeams,
            'myPosition' => $myPosition,
        ]);
    }

    /**
     * Mostra la classifica generale delle squadre
     */
    public function showLeaderboard()
    {
        $myTeam = Auth::user()->playerTeam;

        // Recupera tutte le squadre con i crediti totali guadagnati dalle gare
        $teams = PlayerTeam::with('user')->get()->map(function ($team) {
            // Calcola crediti totali dalle gare
            $totalCredits = 0;
            $racesParticipated = 0;
            $raceDetails = [];

            $lineups = RaceLineup::where('player_team_id', $team->id)
                ->with(['race', 'riders'])
                ->get();

            foreach ($lineups as $lineup) {
                if ($lineup->race && $lineup->race->status === 'completed') {
                    $racesParticipated++;
                    $raceCredits = $lineup->calculateCreditsEarned();
                    $totalCredits += $raceCredits;

                    $raceDetails[] = [
                        'race' => $lineup->race,
                        'credits' => $raceCredits,
                    ];
                }
            }

            return [
                'team' => $team,
                'total_credits' => $totalCredits,
                'races_participated' => $racesParticipated,
                'race_details' => $raceDetails,
                'riders_count' => $team->riders()->count(),
            ];
        })
        ->sortByDesc('total_credits')
        ->values();

        // Trova la posizione della mia squadra
        $myPosition = $teams->search(function ($item) use ($myTeam) {
            return $item['team']->id === $myTeam->id;
        }) + 1;

        // Statistiche globali
        $completedRaces = Race::where('status', 'completed')->count();
        $totalCreditsDistributed = RaceResult::sum('credits_earned');

        return view('leaderboard.index', [
            'teams' => $teams,
            'myTeam' => $myTeam,
            'myPosition' => $myPosition,
            'completedRaces' => $completedRaces,
            'totalCreditsDistributed' => $totalCreditsDistributed,
        ]);
    }

    /**
     * Mostra lo storico crediti di una squadra
     */
    public function showTeamHistory(PlayerTeam $team)
    {
        $myTeam = Auth::user()->playerTeam;

        // Recupera tutte le partecipazioni alle gare
        $raceHistory = RaceLineup::where('player_team_id', $team->id)
            ->with(['race', 'riders'])
            ->get()
            ->filter(function ($lineup) {
                return $lineup->race && $lineup->race->status === 'completed';
            })
            ->map(function ($lineup) {
                $credits = $lineup->calculateCreditsEarned();

                // Dettaglio corridori con risultati
                $riderResults = [];
                foreach ($lineup->riders as $rider) {
                    $result = RaceResult::where('race_id', $lineup->race_id)
                        ->where('rider_id', $rider->id)
                        ->first();

                    if ($result) {
                        $riderResults[] = [
                            'rider' => $rider,
                            'position' => $result->position,
                            'credits' => $result->credits_earned,
                        ];
                    }
                }

                // Ordina per posizione
                usort($riderResults, fn($a, $b) => $a['position'] - $b['position']);

                return [
                    'race' => $lineup->race,
                    'total_credits' => $credits,
                    'rider_results' => $riderResults,
                ];
            })
            ->sortByDesc(fn($item) => $item['race']->date)
            ->values();

        $totalCredits = $raceHistory->sum('total_credits');

        return view('leaderboard.team-history', [
            'team' => $team,
            'raceHistory' => $raceHistory,
            'totalCredits' => $totalCredits,
            'myTeam' => $myTeam,
        ]);
    }
}