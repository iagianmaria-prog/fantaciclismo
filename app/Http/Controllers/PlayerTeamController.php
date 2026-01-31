<?php

namespace App\Http\Controllers;

use App\Models\PlayerTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Rider;
use App\Services\SettingManager;
use App\Models\Trade;
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
                $offeringTeam->balance += $trade->money_adjustment;
                $receivingTeam->balance -= $trade->money_adjustment;
                
                if ($receivingTeam->balance < 0) {
                    throw new Exception('Budget insufficiente per completare lo scambio.');
                }
                
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
     * Mostra il form per creare un counter-offer
     */
    public function showCounterOfferForm(Trade $trade)
    {
        $myTeam = Auth::user()->playerTeam;
        
        if ($trade->receiving_team_id != $myTeam->id) {
            return redirect()->route('market.show')->with('error', 'Non puoi fare una controfferta per questo scambio.');
        }
        
        if ($trade->status !== 'pending') {
            return redirect()->route('market.show')->with('error', 'Questo scambio non è più disponibile.');
        }
        
        $offeredRiders = $trade->riders()->wherePivot('direction', 'offering')->get();
        $requestedRiders = $trade->riders()->wherePivot('direction', 'receiving')->get();
        $myRoster = $myTeam->riders()->with('category')->get();
        $theirRoster = $trade->offeringTeam->riders()->with('category')->get();
        
        return view('market.counter-offer', [
            'originalTrade' => $trade,
            'offeredRiders' => $offeredRiders,
            'requestedRiders' => $requestedRiders,
            'myRoster' => $myRoster,
            'theirRoster' => $theirRoster,
            'myTeam' => $myTeam,
            'theirTeam' => $trade->offeringTeam,
        ]);
    }

    /**
     * Salva il counter-offer
     */
    public function submitCounterOffer(Request $request, Trade $originalTrade)
    {
        $myTeam = Auth::user()->playerTeam;
        
        if ($originalTrade->receiving_team_id != $myTeam->id) {
            return back()->with('error', 'Non puoi fare una controfferta per questo scambio.');
        }
        
        if ($originalTrade->status !== 'pending') {
            return back()->with('error', 'Questo scambio non è più disponibile.');
        }
        
        $request->validate([
            'offered_riders' => 'nullable|array',
            'offered_riders.*' => 'exists:riders,id',
            'requested_riders' => 'nullable|array',
            'requested_riders.*' => 'exists:riders,id',
            'money_adjustment' => 'nullable|integer',
        ]);
        
        if (empty($request->offered_riders) && 
            empty($request->requested_riders) && 
            ($request->money_adjustment == 0 || $request->money_adjustment === null)) {
            return back()->with('error', 'Devi selezionare almeno un corridore o specificare crediti.');
        }
        
        DB::beginTransaction();
        try {
            $originalTrade->status = 'rejected';
            $originalTrade->save();
            
            $counterOffer = Trade::create([
                'offering_team_id' => $myTeam->id,
                'receiving_team_id' => $originalTrade->offering_team_id,
                'money_adjustment' => $request->money_adjustment ?? 0,
                'status' => 'pending',
                'parent_trade_id' => $originalTrade->id,
            ]);
            
            if (!empty($request->offered_riders)) {
                foreach ($request->offered_riders as $riderId) {
                    $counterOffer->riders()->attach($riderId, ['direction' => 'offering']);
                }
            }
            
            if (!empty($request->requested_riders)) {
                foreach ($request->requested_riders as $riderId) {
                    $counterOffer->riders()->attach($riderId, ['direction' => 'receiving']);
                }
            }
            
            DB::commit();
            return redirect()->route('market.show')->with('status', 'Controfferta inviata con successo!');
            
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Errore: ' . $e->getMessage());
        }
    }
}