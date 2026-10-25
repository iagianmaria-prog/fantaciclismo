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
        // Email e password configurabili via ADMIN_EMAIL / ADMIN_PASSWORD nel .env
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        // Controlla se l'utente admin esiste già per evitare duplicati
        if (!User::where('email', $email)->exists()) {

            User::create([
                'name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($password),
            ]);

        }
    }
}

