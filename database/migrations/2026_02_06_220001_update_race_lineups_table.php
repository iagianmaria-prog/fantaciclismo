<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_lineups', function (Blueprint $table) {
            $table->foreignId('race_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_team_id')->constrained()->onDelete('cascade');

            // Una squadra può avere una sola formazione per gara
            $table->unique(['race_id', 'player_team_id']);
        });

        // Tabella pivot per i corridori nella formazione
        Schema::create('race_lineup_rider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_lineup_id')->constrained()->onDelete('cascade');
            $table->foreignId('rider_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Un corridore può essere schierato una sola volta per formazione
            $table->unique(['race_lineup_id', 'rider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_lineup_rider');

        Schema::table('race_lineups', function (Blueprint $table) {
            $table->dropForeign(['race_id']);
            $table->dropForeign(['player_team_id']);
            $table->dropUnique(['race_id', 'player_team_id']);
            $table->dropColumn(['race_id', 'player_team_id']);
        });
    }
};
