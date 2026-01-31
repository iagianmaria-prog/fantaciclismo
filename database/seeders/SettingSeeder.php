<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\SettingManager; // Importiamo il nostro gestore di impostazioni

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Questo metodo viene eseguito da `db:seed` e si assicura
     * che tutte le impostazioni di gioco di base esistano nel database.
     */
    public function run(): void
    {
        // Chiamiamo il metodo statico che abbiamo creato in precedenza.
        // Questo metodo controlla ogni chiave nell'array 'specialKeys'
        // e la crea nel database se non esiste già.
        // È un modo pulito e centralizzato per gestire le impostazioni di default.
        SettingManager::ensureSpecialKeysExist();
    }
}
