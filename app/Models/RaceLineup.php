<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RaceLineup extends Model
{
    protected $fillable = [
        'race_id',
        'player_team_id',
    ];

    // Relazioni
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function playerTeam(): BelongsTo
    {
        return $this->belongsTo(PlayerTeam::class);
    }

    public function riders(): BelongsToMany
    {
        return $this->belongsToMany(Rider::class, 'race_lineup_rider')
                    ->withTimestamps();
    }

    // Helpers
    public function getRidersCount(): int
    {
        return $this->riders()->count();
    }

    public function isComplete(): bool
    {
        return $this->getRidersCount() >= $this->race->lineup_size;
    }

    public function canAddRider(): bool
    {
        return $this->getRidersCount() < $this->race->lineup_size;
    }

    public function hasRider(Rider $rider): bool
    {
        return $this->riders()->where('rider_id', $rider->id)->exists();
    }

    // Calcola crediti totali guadagnati dalla formazione
    public function calculateCreditsEarned(): int
    {
        $totalCredits = 0;

        foreach ($this->riders as $rider) {
            $result = RaceResult::where('race_id', $this->race_id)
                                ->where('rider_id', $rider->id)
                                ->first();

            if ($result) {
                $totalCredits += $result->credits_earned;
            }
        }

        return $totalCredits;
    }
}
