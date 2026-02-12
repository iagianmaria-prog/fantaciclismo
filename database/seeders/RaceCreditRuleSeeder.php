<?php

namespace Database\Seeders;

use App\Models\RaceCreditRule;
use Illuminate\Database\Seeder;

class RaceCreditRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Crediti per classiche
        $classicaCredits = [
            1 => 100,
            2 => 80,
            3 => 70,
            4 => 60,
            5 => 55,
            6 => 50,
            7 => 45,
            8 => 40,
            9 => 35,
            10 => 30,
            11 => 25,
            12 => 22,
            13 => 20,
            14 => 18,
            15 => 16,
            16 => 14,
            17 => 12,
            18 => 10,
            19 => 8,
            20 => 6,
        ];

        foreach ($classicaCredits as $position => $credits) {
            RaceCreditRule::updateOrCreate(
                ['race_type' => 'classica', 'position' => $position],
                ['credits' => $credits]
            );
        }

        // Crediti per tappe (leggermente inferiori)
        $tappaCredits = [
            1 => 50,
            2 => 40,
            3 => 35,
            4 => 30,
            5 => 27,
            6 => 25,
            7 => 22,
            8 => 20,
            9 => 17,
            10 => 15,
            11 => 12,
            12 => 10,
            13 => 9,
            14 => 8,
            15 => 7,
            16 => 6,
            17 => 5,
            18 => 4,
            19 => 3,
            20 => 2,
        ];

        foreach ($tappaCredits as $position => $credits) {
            RaceCreditRule::updateOrCreate(
                ['race_type' => 'tappa', 'position' => $position],
                ['credits' => $credits]
            );
        }

        // Crediti per cronometro
        $cronoCredits = [
            1 => 40,
            2 => 32,
            3 => 28,
            4 => 24,
            5 => 22,
            6 => 20,
            7 => 18,
            8 => 16,
            9 => 14,
            10 => 12,
        ];

        foreach ($cronoCredits as $position => $credits) {
            RaceCreditRule::updateOrCreate(
                ['race_type' => 'cronometro', 'position' => $position],
                ['credits' => $credits]
            );
        }
    }
}
