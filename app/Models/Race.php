<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Race extends Model
{
    protected $fillable = [
        'name',
        'date',
        'type',
        'lineup_size',
        'lineup_deadline',
        'status',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'lineup_deadline' => 'datetime',
    ];

    // Relazioni
    public function lineups(): HasMany
    {
        return $this->hasMany(RaceLineup::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeLineupOpen($query)
    {
        return $query->where('status', 'lineup_open');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helpers
    public function isLineupOpen(): bool
    {
        if ($this->status !== 'lineup_open') {
            return false;
        }

        if ($this->lineup_deadline && Carbon::now()->gt($this->lineup_deadline)) {
            return false;
        }

        return true;
    }

    public function canSubmitLineup(): bool
    {
        return $this->isLineupOpen();
    }

    public function hasResults(): bool
    {
        return $this->results()->exists();
    }

    public function getLineupForTeam(PlayerTeam $team): ?RaceLineup
    {
        return $this->lineups()->where('player_team_id', $team->id)->first();
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'upcoming' => 'In arrivo',
            'lineup_open' => 'Formazioni aperte',
            'in_progress' => 'In corso',
            'completed' => 'Completata',
            default => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'classica' => 'Classica',
            'tappa' => 'Tappa',
            'cronometro' => 'Cronometro',
            default => $this->type,
        };
    }
}
