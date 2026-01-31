<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerTeam extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'name', 'balance'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function riders(): HasMany
    {
        return $this->hasMany(Rider::class);
    }

    public function proposedTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'offering_team_id');
    }

    public function receivedTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'receiving_team_id');
    }
}
