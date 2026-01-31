<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prendiamo l'utente loggato
        $user = Auth::user();

        // Se l'utente non è loggato, non dovrebbe nemmeno essere qui, ma per sicurezza lo reindirizziamo.
        if (!$user) {
            return redirect()->route('login');
        }

        // === MODIFICA FONDAMENTALE ===
        // Controlliamo se la relazione 'playerTeam' NON esiste.
        // Questo è il modo più sicuro e inequivocabile per fare questo controllo.
        if (!$user->playerTeam()->exists()) {
            // Se NON ha una squadra, lo mandiamo a crearla.
            return redirect()->route('player-team.create');
        }
        
        // Se il controllo passa, significa che l'utente ha una squadra. Può procedere.
        return $next($request);
    }
}
