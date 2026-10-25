<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  // In ..._create_player_teams_table.php

public function up(): void
{
    Schema::create('player_teams', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('name')->unique();
        $table->integer('balance'); // Saldo in fantamilioni
        $table->timestamps();
    });

    // Aggiunge il vincolo rimandato dalla migration create_riders_table
    // (riders viene creata prima di player_teams)
    Schema::table('riders', function (Blueprint $table) {
        $table->foreign('player_team_id')->references('id')->on('player_teams');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropForeign(['player_team_id']);
        });

        Schema::dropIfExists('player_teams');
    }
};
