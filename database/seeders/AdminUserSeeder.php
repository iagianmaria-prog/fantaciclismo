<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Importante per la password
use App\Models\User; // Importa il modello User

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Controlla se l'utente admin esiste già per evitare duplicati
        if (!User::where('email', 'admin@test.com')->exists()) {
            
            User::create([
                'name' => 'Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'), // Imposta la password a 'password'
            ]);

        }
    }
}

