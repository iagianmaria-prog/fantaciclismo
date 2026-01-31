<?php

namespace App\Models;

// Questi 'use' sono fondamentali
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasOne;

// La classe DEVE implementare FilamentUser
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * La relazione con la squadra del giocatore.
     */
    public function playerTeam(): HasOne
    {
        return $this->hasOne(PlayerTeam::class);
    }

    /**
     * Questo metodo controlla l'accesso al pannello admin.
     * È l'unica cosa che Filament usa per decidere se farti entrare.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Assicurati che questa sia l'email esatta del tuo utente admin.
        // Mettila tra apici singoli.
        return $this->email === 'admin@test.com';
    }
}
