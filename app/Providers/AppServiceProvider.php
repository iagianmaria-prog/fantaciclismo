<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Filament\Resources\SettingResource; // <-- Aggiungi questa importazione
use Illuminate\Support\Facades\Schema;      // <-- Aggiungi questa importazione
use App\Models\Setting;                      // <-- Aggiungi questa importazione

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Questo è il posto giusto!
        // Il metodo boot() di AppServiceProvider viene eseguito dopo che tutti
        // i servizi, inclusa la connessione al database, sono stati registrati.

        try {
            // Aggiungiamo un controllo per assicurarci che la tabella 'settings' esista.
            // Questo previene errori durante il primo 'php artisan migrate'.
            if (Schema::hasTable('settings')) {
                $this->ensureSpecialKeysExist();
            }
        } catch (\Exception $e) {
            // In caso di problemi di connessione al DB durante l'installazione, ignora l'errore.
            // Questo rende il comando 'php artisan serve' più robusto.
        }
    }

    /**
     * Assicura che le impostazioni di gioco chiave esistano nel database.
     * Questo metodo è stato spostato qui da SettingResource per evitare problemi di caricamento.
     */
    private function ensureSpecialKeysExist(): void
    {
        $defaults = [
            'initial_budget' => ['value' => 250, 'description' => 'Budget iniziale per ogni squadra.'],
            'repair_auction_recovery' => ['value' => 50, 'description' => 'Percentuale del valore recuperata svincolando un corridore.'],
            'trade_strict_validation' => ['value' => '0', 'description' => 'Abilita validazione rigida su categorie rosa durante scambi (1=SI, 0=NO).'],
            'max_gc' => ['value' => 2, 'description' => 'Numero massimo di corridori per la categoria GC.'],
            'max_velocista' => ['value' => 4, 'description' => 'Numero massimo di corridori per la categoria Velocista.'],
            'max_puncher' => ['value' => 4, 'description' => 'Numero massimo di corridori per la categoria Puncher.'],
            'max_pave' => ['value' => 2, 'description' => 'Numero massimo di corridori per la categoria Pavé.'],
            'max_next gen' => ['value' => 2, 'description' => 'Numero massimo di corridori per la categoria Next Gen.'],
            'max_gregario' => ['value' => 8, 'description' => 'Numero massimo di corridori per la categoria Gregario.'],
            'max_cronoman' => ['value' => 1, 'description' => 'Numero massimo di corridori per la categoria Cronoman.'],
        ];

        foreach ($defaults as $key => $data) {
            Setting::firstOrCreate(['key' => $key], $data);
        }
    }
}
