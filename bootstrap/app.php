<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) { // NOTA: ho rimosso ': void' per compatibilità
        // === INIZIO CODICE CORRETTO ===
        $middleware->alias([
            'has.team' => \App\Http\Middleware\EnsureUserHasTeam::class,
        ]);
        // === FINE CODICE CORRETTO ===
    })
    ->withExceptions(function (Exceptions $exceptions) { // NOTA: ho rimosso ': void'
        //
    })->create();
