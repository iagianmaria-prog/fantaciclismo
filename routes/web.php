<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerTeamController;
use App\Http\Controllers\RaceController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $team = Auth::user()->playerTeam()->with('riders.category')->first();
    return view('dashboard', ['team' => $team]);
})->middleware(['auth', 'verified', 'has.team'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rotte per la creazione della squadra
    Route::get('/create-team', [PlayerTeamController::class, 'create'])->name('player-team.create');
    Route::post('/create-team', [PlayerTeamController::class, 'store'])->name('player-team.store');

    // Rotte per l'asta
    Route::get('/auction', [PlayerTeamController::class, 'showAuction'])->name('auction.show');
    Route::post('/auction/buy/{rider}', [PlayerTeamController::class, 'buyRider'])->name('auction.buy');

    // Rotta per lo svincolo
    Route::post('/roster/release/{rider}', [PlayerTeamController::class, 'releaseRider'])->name('roster.release');

    // Rotta per il mercato
    Route::get('/market', [PlayerTeamController::class, 'showMarket'])->name('market.show')->middleware('has.team');
    
    // Rotte per gestione scambi
    Route::post('/market/accept/{trade}', [PlayerTeamController::class, 'acceptTrade'])
        ->name('market.accept')
        ->middleware('has.team');
        
    Route::post('/market/reject/{trade}', [PlayerTeamController::class, 'rejectTrade'])
        ->name('market.reject')
        ->middleware('has.team');
        
Route::post('/market/cancel/{trade}', [PlayerTeamController::class, 'cancelTrade'])
        ->name('market.cancel')
        ->middleware('has.team');
    
// Rotta per storico scambi
    Route::get('/market/history', [PlayerTeamController::class, 'showHistory'])
        ->name('market.history')
        ->middleware('has.team');
    
    // Rotta per statistiche squadra
    Route::get('/statistics', [PlayerTeamController::class, 'showStatistics'])
        ->name('statistics.show')
        ->middleware('has.team');

    // Rotte per le gare
    Route::middleware('has.team')->group(function () {
        Route::get('/races', [RaceController::class, 'index'])->name('races.index');
        Route::get('/races/{race}', [RaceController::class, 'show'])->name('races.show');
        Route::get('/races/{race}/lineup', [RaceController::class, 'lineup'])->name('races.lineup');
        Route::post('/races/{race}/lineup', [RaceController::class, 'saveLineup'])->name('races.lineup.save');
        Route::get('/races/{race}/standings', [RaceController::class, 'standings'])->name('races.standings');
    });

});

require __DIR__.'/auth.php';