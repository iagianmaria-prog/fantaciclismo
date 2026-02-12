<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaceCreditRule extends Model
{
    protected $fillable = [
        'race_type',
        'position',
        'credits',
    ];

    // Ottieni tutti i crediti per un tipo di gara
    public static function getCreditsForType(string $raceType): array
    {
        return self::where('race_type', $raceType)
                   ->orderBy('position')
                   ->pluck('credits', 'position')
                   ->toArray();
    }
}
