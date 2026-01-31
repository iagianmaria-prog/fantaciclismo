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
        'player_team_id',
    ];

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
