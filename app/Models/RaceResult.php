<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    protected $fillable = [
        'race_id',
        'rider_id',
        'position',
        'credits_earned',
    ];

    // Relazioni
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    // Calcola automaticamente i crediti in base alla posizione e tipo gara
    public static function calculateCredits(string $raceType, int $position): int
    {
        $rule = RaceCreditRule::where('race_type', $raceType)
                              ->where('position', $position)
                              ->first();

        return $rule ? $rule->credits : 0;
    }
}
