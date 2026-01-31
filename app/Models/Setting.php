<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     *  Questo è FONDAMENTALE.
     *  Dice a Laravel quali campi possono essere "riempiti" in massa.
     *  Senza questo, l'operazione di modifica fallisce per sicurezza.
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     *  Questo metodo viene eseguito automaticamente ogni volta che un'impostazione
     *  viene salvata o aggiornata. Lo usiamo per pulire la cache.
     */
    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            // Pulisce la cache per questa specifica impostazione,
            // forzando l'applicazione a rileggerla dal database la prossima volta.
            Cache::forget('setting.' . $setting->key);
        });
    }
}
