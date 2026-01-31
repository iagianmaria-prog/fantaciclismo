<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SettingManager; // Importiamo il nostro manager

class EnsureSettingsExist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Eseguiamo il nostro controllo qui.
        // In questo punto del codice, il database è sicuramente disponibile.
        SettingManager::ensureSpecialKeysExist();

        // Dopo il controllo, lascia che la richiesta prosegua normalmente.
        return $next($request);
    }
}
