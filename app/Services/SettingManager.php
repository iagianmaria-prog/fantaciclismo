<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingManager
{
    /**
     * Array delle chiavi speciali con i loro valori di default e descrizioni.
     * Questo è il "cervello" delle nostre regole di gioco.
     */
    public static array $specialKeys = [
        // Rosa e Contratti
        'team_size' => ['default' => 45, 'description' => 'Numero totale di ciclisti per squadra'],
        'contract_duration_initial' => ['default' => 2, 'description' => 'Durata contratto per acquisti asta iniziale (anni)'],
        'contract_duration_repair' => ['default' => 1.5, 'description' => 'Durata contratto per acquisti aste di riparazione (anni)'],
        'max_gc' => ['default' => 8, 'description' => 'Numero massimo corridori GC'],
        'max_puncher' => ['default' => 8, 'description' => 'Numero massimo corridori Puncher'],
        'max_pave' => ['default' => 5, 'description' => 'Numero massimo corridori Pavé'],
        'max_velocisti' => ['default' => 7, 'description' => 'Numero massimo corridori Velocisti'],
        'max_cronomen' => ['default' => 3, 'description' => 'Numero massimo corridori Cronomen'],
        'max_gregari' => ['default' => 6, 'description' => 'Numero massimo corridori Gregari'],
        'max_next_gen' => ['default' => 8, 'description' => 'Numero massimo corridori Next Gen'],

        // Aste e Budget
        'initial_budget' => ['default' => 700, 'description' => 'Budget iniziale per il primo anno (milioni)'],
        
        // Multe
        'rebuy_penalty_amount' => ['default' => 25, 'description' => 'Multa per riacquisto di un ciclista appena svincolato (milioni)'],

        // Svincoli
        'release_recovery_percentage_pre_season' => ['default' => 100, 'description' => '% di recupero per svincoli prima della stagione'],
        'release_recovery_percentage_mid_season' => ['default' => 50, 'description' => '% di recupero per svincoli durante la stagione'],

        // Scambi e Prestiti
        'max_trades_per_team' => ['default' => 5, 'description' => 'Numero massimo di scambi tra squadre'],
        'max_new_signings_from_free_agents' => ['default' => 5, 'description' => 'Numero massimo di acquisti da svincolati'],

        // Pagamenti Post-datati
        'max_postponed_payment_amount' => ['default' => 100, 'description' => 'Massimale di milioni postdatabili a fine stagione'],

        // Cartellino e Svalutazione
        'annual_devaluation_percentage' => ['default' => 20, 'description' => '% di svalutazione annuale del cartellino'],

        // Stipendi
        'salary_percentage' => ['default' => 20, 'description' => '% del valore d\'acquisto per calcolare lo stipendio'],
        'min_salary_amount' => ['default' => 1, 'description' => 'Stipendio minimo garantito (milioni)'],
        'forced_release_recovery_percentage' => ['default' => 50, 'description' => '% di recupero per svincolo forzato per mancato pagamento stipendi'],

        // Conferme di Fine Stagione
        'confirmations_gc' => ['default' => 5, 'description' => 'Numero conferme per GC'],
        'confirmations_puncher' => ['default' => 5, 'description' => 'Numero conferme per Puncher'],
        'confirmations_pave' => ['default' => 3, 'description' => 'Numero conferme per Pavé'],
        'confirmations_velocisti' => ['default' => 4, 'description' => 'Numero conferme per Velocisti'],
        'confirmations_crono' => ['default' => 2, 'description' => 'Numero conferme per Cronomen'],
        'confirmations_next' => ['default' => 5, 'description' => 'Numero conferme per Next Gen'],
        'confirmations_gregario' => ['default' => 4, 'description' => 'Numero conferme per Gregari'],
        'confirmations_free_choice' => ['default' => 5, 'description' => 'Numero conferme jolly a scelta'],
    ];

    /**
     * Assicura che tutte le chiavi speciali esistano nel database.
     */
    public static function ensureSpecialKeysExist(): void
    {
        foreach (self::$specialKeys as $key => $details) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $details['default']]
            );
        }
    }

    /**
     * Recupera un valore dal database.
     */
    public static function get(string $key)
    {
        return Cache::rememberForever('setting.'.$key, function () use ($key) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : (self::$specialKeys[$key]['default'] ?? null);
        });
    }
}
