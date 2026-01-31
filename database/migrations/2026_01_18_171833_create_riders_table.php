<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            // QUESTA È LA RIGA CORRETTA
            $table->string('team_name')->nullable(); // Nome della squadra reale del corridore (opzionale)
            
            $table->integer('initial_value'); // Valore in fantamilioni

            $table->foreignId('rider_category_id')->constrained('rider_categories');
            
            $table->foreignId('player_team_id')->nullable()->constrained('player_teams');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
