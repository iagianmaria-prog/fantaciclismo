<?php

namespace App\Filament\Pages;

use App\Models\PlayerTeam;
use App\Models\Race;
use App\Models\RaceLineup;
use App\Models\RaceResult;
use App\Models\Rider;
use App\Models\Trade;
use App\Services\SettingManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LeagueManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.league-management';

    protected static ?string $navigationLabel = 'Gestione Lega';

    protected static ?string $title = 'Gestione Lega';

    protected static ?int $navigationSort = 100;

    public function getStats(): array
    {
        return [
            'teams' => PlayerTeam::count(),
            'riders_assigned' => Rider::whereNotNull('player_team_id')->count(),
            'riders_free' => Rider::whereNull('player_team_id')->count(),
            'races' => Race::count(),
            'races_completed' => Race::where('status', 'completed')->count(),
            'lineups' => RaceLineup::count(),
            'results' => RaceResult::count(),
            'trades_total' => Trade::count(),
            'trades_pending' => Trade::where('status', 'pending')->count(),
            'trades_accepted' => Trade::where('status', 'accepted')->count(),
            'expiring_contracts' => Rider::whereNotNull('player_team_id')
                ->where('contract_remaining_years', '<=', 1)
                ->count(),
            'expired_contracts' => Rider::whereNotNull('player_team_id')
                ->where('contract_remaining_years', '<=', 0)
                ->count(),
        ];
    }

    public function resetTeamsBudget(): void
    {
        $initialBudget = SettingManager::get('initial_budget');
        PlayerTeam::query()->update(['balance' => $initialBudget]);

        Notification::make()
            ->title('Budget resettato')
            ->body('Tutte le squadre hanno ora ' . $initialBudget . 'M di budget.')
            ->success()
            ->send();
    }

    public function releaseAllRiders(): void
    {
        Rider::query()->update(['player_team_id' => null]);

        Notification::make()
            ->title('Corridori svincolati')
            ->body('Tutti i corridori sono stati svincolati.')
            ->success()
            ->send();
    }

    public function deleteAllTrades(): void
    {
        DB::table('rider_trade')->delete();
        Trade::query()->delete();

        Notification::make()
            ->title('Scambi eliminati')
            ->body('Tutti gli scambi sono stati eliminati.')
            ->success()
            ->send();
    }

    public function deleteAllRaceData(): void
    {
        DB::table('race_lineup_rider')->delete();
        RaceLineup::query()->delete();
        RaceResult::query()->delete();
        Race::query()->update(['status' => 'upcoming']);

        Notification::make()
            ->title('Dati gare eliminati')
            ->body('Risultati e formazioni eliminati. Le gare sono state resettate.')
            ->success()
            ->send();
    }

    public function fullReset(): void
    {
        DB::beginTransaction();

        try {
            DB::table('rider_trade')->delete();
            Trade::query()->delete();
            DB::table('race_lineup_rider')->delete();
            RaceLineup::query()->delete();
            RaceResult::query()->delete();
            Race::query()->update(['status' => 'upcoming']);
            Rider::query()->update(['player_team_id' => null]);

            $initialBudget = SettingManager::get('initial_budget');
            PlayerTeam::query()->update(['balance' => $initialBudget]);

            DB::commit();

            Notification::make()
                ->title('Reset completato')
                ->body('La lega è stata resettata. Puoi ricominciare!')
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Errore')
                ->body('Errore durante il reset: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteAllTeams(): void
    {
        DB::beginTransaction();

        try {
            DB::table('rider_trade')->delete();
            Trade::query()->delete();
            DB::table('race_lineup_rider')->delete();
            RaceLineup::query()->delete();
            Rider::query()->update(['player_team_id' => null]);
            PlayerTeam::query()->delete();
            Race::query()->update(['status' => 'upcoming']);
            RaceResult::query()->delete();

            DB::commit();

            Notification::make()
                ->title('Squadre eliminate')
                ->body('Tutte le squadre sono state eliminate.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Errore')
                ->body('Errore: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Decrementa di 1 anno tutti i contratti dei corridori sotto contratto.
     * Da usare a fine stagione.
     */
    public function decrementContracts(): void
    {
        $updated = Rider::whereNotNull('player_team_id')
            ->whereNotNull('contract_remaining_years')
            ->where('contract_remaining_years', '>', 0)
            ->decrement('contract_remaining_years');

        Notification::make()
            ->title('Contratti aggiornati')
            ->body("Decrementato 1 anno a {$updated} contratti.")
            ->success()
            ->send();
    }

    /**
     * Svincola automaticamente i corridori con contratto scaduto (0 anni rimanenti).
     */
    public function releaseExpiredContracts(): void
    {
        $expiredRiders = Rider::whereNotNull('player_team_id')
            ->where('contract_remaining_years', '<=', 0)
            ->get();

        $count = $expiredRiders->count();

        if ($count === 0) {
            Notification::make()
                ->title('Nessun contratto scaduto')
                ->body('Non ci sono corridori con contratto scaduto.')
                ->info()
                ->send();
            return;
        }

        foreach ($expiredRiders as $rider) {
            $rider->player_team_id = null;
            $rider->contract_years = null;
            $rider->contract_remaining_years = null;
            $rider->contract_start_date = null;
            $rider->save();
        }

        Notification::make()
            ->title('Contratti scaduti gestiti')
            ->body("{$count} corridori sono stati svincolati per contratto scaduto.")
            ->success()
            ->send();
    }

    /**
     * Esegue tutte le operazioni di fine stagione:
     * 1. Decrementa contratti
     * 2. Svincola contratti scaduti
     */
    public function endSeason(): void
    {
        DB::beginTransaction();

        try {
            // 1. Decrementa tutti i contratti
            $decremented = Rider::whereNotNull('player_team_id')
                ->whereNotNull('contract_remaining_years')
                ->where('contract_remaining_years', '>', 0)
                ->decrement('contract_remaining_years');

            // 2. Svincola i corridori con contratto scaduto
            $expiredRiders = Rider::whereNotNull('player_team_id')
                ->where('contract_remaining_years', '<=', 0)
                ->get();

            $expiredCount = $expiredRiders->count();

            foreach ($expiredRiders as $rider) {
                $rider->player_team_id = null;
                $rider->contract_years = null;
                $rider->contract_remaining_years = null;
                $rider->contract_start_date = null;
                $rider->save();
            }

            DB::commit();

            Notification::make()
                ->title('Fine Stagione Completata')
                ->body("Contratti decrementati: {$decremented}. Corridori svincolati per scadenza: {$expiredCount}.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Errore')
                ->body('Errore durante fine stagione: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
