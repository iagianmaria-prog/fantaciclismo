<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiderCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'GC', 'description' => 'Corridori per la classifica generale'],
            ['name' => 'Velocista', 'description' => 'Corridori per gli sprint'],
            ['name' => 'Scalatore', 'description' => 'Corridori per le tappe di montagna'],
            ['name' => 'Passista', 'description' => 'Corridori per le tappe pianeggianti e cronometro'],
            ['name' => 'Altro', 'description' => 'Corridori con altre specialità'],
        ];

        // Inserisce le categorie solo se non esistono già (firstOrCreate):
        // TRUNCATE fallirebbe su MySQL/Postgres quando la tabella è
        // referenziata dai corridori, e cancellerebbe dati in produzione.
        foreach ($categories as $category) {
            DB::table('rider_categories')->updateOrInsert(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
