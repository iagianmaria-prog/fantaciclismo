<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Questo è il seeder "principale" che orchestra tutti gli altri.
     * L'ordine in cui vengono chiamati i seeder può essere importante.
     */
    public function run(): void
    {
        // Usiamo un singolo array per chiamare tutti i seeder necessari.
        $this->call([
            // 1. Crea le categorie di corridori (GC, Velocista, etc.)
            RiderCategorySeeder::class,

            // 2. Crea l'utente amministratore di default
            AdminUserSeeder::class,

            // 3. (NUOVO) Crea le impostazioni di gioco di default (initial_budget, etc.)
            SettingSeeder::class,
        ]);
    }
}
