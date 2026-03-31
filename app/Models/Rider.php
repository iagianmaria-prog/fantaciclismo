<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rider_category_id',
        'initial_value',
        'current_value',
        'purchase_price',
        'player_team_id',
        'real_team',
        'contract_years',
        'contract_remaining_years',
        'contract_start_date',
    ];

    protected $casts = [
        'contract_start_date' => 'date',
    ];

    /**
     * Ritorna il valore effettivo del corridore.
     * Se current_value è impostato, usa quello, altrimenti initial_value.
     */
    public function getEffectiveValueAttribute(): int
    {
        return $this->current_value ?? $this->initial_value;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RiderCategory::class, 'rider_category_id');
    }

    public function playerTeam(): BelongsTo
    {
        return $this->belongsTo(PlayerTeam::class);
    }

    /**
     * Gli scambi in cui questo corridore è coinvolto.
     */
    public function trades(): BelongsToMany
    {
        return $this->belongsToMany(Trade::class, 'rider_trade');
    }
}
