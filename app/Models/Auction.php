<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Allinea gli stati delle aste in base a data/ora correnti:
     * apre le aste programmate il cui inizio è passato e
     * chiude le aste aperte la cui fine è passata.
     */
    public static function syncStatuses(): void
    {
        $now = now();

        static::where('status', 'scheduled')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now)
            ->update(['status' => 'open']);

        static::where('status', '!=', 'closed')
            ->where('ends_at', '<=', $now)
            ->update(['status' => 'closed']);
    }

    /**
     * Ritorna l'asta attualmente aperta (dopo aver allineato gli stati).
     */
    public static function active(): ?self
    {
        static::syncStatuses();

        return static::where('status', 'open')->first();
    }
}
