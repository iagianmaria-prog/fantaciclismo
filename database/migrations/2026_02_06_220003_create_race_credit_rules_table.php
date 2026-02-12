<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabella per definire i crediti per ogni posizione
        Schema::create('race_credit_rules', function (Blueprint $table) {
            $table->id();
            $table->string('race_type'); // classica, tappa, cronometro, etc.
            $table->integer('position'); // 1, 2, 3, ...
            $table->integer('credits'); // Crediti assegnati
            $table->timestamps();

            $table->unique(['race_type', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_credit_rules');
    }
};
