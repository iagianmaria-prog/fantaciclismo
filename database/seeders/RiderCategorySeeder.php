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

        // Svuota la tabella prima di inserirli per evitare duplicati
        DB::table('rider_categories')->truncate();
        
        // Inserisce le categorie nel database
        DB::table('rider_categories')->insert($categories);
    }
}
