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
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetTeamsBudget')
                ->label('Reset Budget Squadre')
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset Budget Squadre')
                ->modalDescription('Questa azione resetterà il budget di tutte le squadre al valore iniziale. I corridori rimarranno assegnati.')
                ->action(function () {
                    $initialBudget = SettingManager::get('initial_budget');
                    PlayerTeam::query()->update(['balance' => $initialBudget]);

                    Notification::make()
                        ->title('Budget resettato')
                        ->body('Tutte le squadre hanno ora ' . $initialBudget . 'M di budget.')
                        ->success()
                        ->send();
                }),

            Action::make('releaseAllRiders')
                ->label('Svincola Tutti i Corridori')
                ->icon('heroicon-o-user-minus')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Svincola Tutti i Corridori')
                ->modalDescription('Questa azione rimuoverà tutti i corridori dalle squadre. I corridori torneranno disponibili all\'asta.')
                ->action(function () {
                    Rider::query()->update(['player_team_id' => null]);

                    Notification::make()
                        ->title('Corridori svincolati')
                        ->body('Tutti i corridori sono stati svincolati.')
                        ->success()
                        ->send();
                }),

            Action::make('deleteAllTrades')
                ->label('Elimina Tutti gli Scambi')
                ->icon('heroicon-o-arrows-right-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Elimina Tutti gli Scambi')
                ->modalDescription('Questa azione eliminerà tutto lo storico degli scambi.')
                ->action(function () {
                    DB::table('rider_trade')->delete();
                    Trade::query()->delete();

                    Notification::make()
                        ->title('Scambi eliminati')
                        ->body('Tutti gli scambi sono stati eliminati.')
                        ->success()
                        ->send();
                }),

            Action::make('deleteAllRaceData')
                ->label('Elimina Risultati e Formazioni')
                ->icon('heroicon-o-flag')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Elimina Risultati e Formazioni')
                ->modalDescription('Questa azione eliminerà tutti i risultati delle gare e le formazioni schierate. Le gare rimarranno.')
                ->action(function () {
                    DB::table('race_lineup_rider')->delete();
                    RaceLineup::query()->delete();
                    RaceResult::query()->delete();

                    // Reset stato gare a upcoming
                    Race::query()->update(['status' => 'upcoming']);

                    Notification::make()
                        ->title('Dati gare eliminati')
                        ->body('Risultati e formazioni eliminati. Le gare sono state resettate.')
                        ->success()
                        ->send();
                }),

            Action::make('fullReset')
                ->label('RESET COMPLETO LEGA')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('ATTENZIONE: Reset Completo')
                ->modalDescription('Questa azione eliminerà: tutti gli scambi, tutti i risultati, tutte le formazioni, e svincola tutti i corridori resettando il budget. Le squadre, le gare e i corridori rimarranno. Sei sicuro?')
                ->modalSubmitActionLabel('Sì, resetta tutto')
                ->action(function () {
                    DB::beginTransaction();

                    try {
                        // Elimina scambi
                        DB::table('rider_trade')->delete();
                        Trade::query()->delete();

                        // Elimina dati gare
                        DB::table('race_lineup_rider')->delete();
                        RaceLineup::query()->delete();
                        RaceResult::query()->delete();

                        // Reset gare
                        Race::query()->update(['status' => 'upcoming']);

                        // Svincola corridori
                        Rider::query()->update(['player_team_id' => null]);

                        // Reset budget
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
                }),

            Action::make('deleteAllTeams')
                ->label('ELIMINA TUTTE LE SQUADRE')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('ATTENZIONE: Elimina Tutte le Squadre')
                ->modalDescription('Questa azione eliminerà TUTTE le squadre e tutti i dati correlati (scambi, formazioni, risultati). Gli utenti e i corridori rimarranno. Sei sicuro?')
                ->modalSubmitActionLabel('Sì, elimina tutto')
                ->action(function () {
                    DB::beginTransaction();

                    try {
                        // Elimina in ordine di dipendenza
                        DB::table('rider_trade')->delete();
                        Trade::query()->delete();
                        DB::table('race_lineup_rider')->delete();
                        RaceLineup::query()->delete();
                        Rider::query()->update(['player_team_id' => null]);
                        PlayerTeam::query()->delete();

                        // Reset gare
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
                }),
        ];
    }
}
