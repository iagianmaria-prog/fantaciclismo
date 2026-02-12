<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_results', function (Blueprint $table) {
            $table->foreignId('race_id')->constrained()->onDelete('cascade');
            $table->foreignId('rider_id')->constrained()->onDelete('cascade');
            $table->integer('position'); // Posizione in classifica (1, 2, 3, ...)
            $table->integer('credits_earned')->default(0); // Crediti guadagnati

            // Un corridore può avere un solo risultato per gara
            $table->unique(['race_id', 'rider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('race_results', function (Blueprint $table) {
            $table->dropForeign(['race_id']);
            $table->dropForeign(['rider_id']);
            $table->dropUnique(['race_id', 'rider_id']);
            $table->dropColumn(['race_id', 'rider_id', 'position', 'credits_earned']);
        });
    }
};
