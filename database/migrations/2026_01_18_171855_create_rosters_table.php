<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // In ..._create_rosters_table.php

public function up(): void
{
    Schema::create('rosters', function (Blueprint $table) {
        $table->id();
        $table->foreignId('team_id')->constrained('teams');
        $table->foreignId('rider_id')->constrained('riders');
        $table->unsignedInteger('purchase_value'); // Valore a cui è stato comprato
        $table->unsignedTinyInteger('contract_years_left')->default(3); // Per Fase 2
        $table->timestamps();

        // Impedisce di aggiungere lo stesso corridore due volte alla stessa squadra
        $table->unique(['team_id', 'rider_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rosters');
    }
};
