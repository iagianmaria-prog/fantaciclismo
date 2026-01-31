<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Trade extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'offering_team_id', 
        'receiving_team_id', 
        'money_adjustment', 
        'status',
        'parent_trade_id'
    ];

    public function offeringTeam(): BelongsTo
    {
        return $this->belongsTo(PlayerTeam::class, 'offering_team_id');
    }

    public function receivingTeam(): BelongsTo
    {
        return $this->belongsTo(PlayerTeam::class, 'receiving_team_id');
    }

    public function riders(): BelongsToMany
    {
        return $this->belongsToMany(Rider::class, 'rider_trade')->withPivot('direction')->withTimestamps();
    }

    public function offeredRiders()
    {
        return $this->riders()->wherePivot('direction', 'offering');
    }

    public function requestedRiders()
    {
        return $this->riders()->wherePivot('direction', 'receiving');
    }

    // Relazione con lo scambio originale (se questo è un counter-offer)
    public function parentTrade(): BelongsTo
    {
        return $this->belongsTo(Trade::class, 'parent_trade_id');
    }

    // Controfferte ricevute per questo scambio
    public function counterOffers()
    {
        return $this->hasMany(Trade::class, 'parent_trade_id');
    }

    // Verifica se questo trade è un counter-offer
    public function isCounterOffer(): bool
    {
        return $this->parent_trade_id !== null;
    }
}