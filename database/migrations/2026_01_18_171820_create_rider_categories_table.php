<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Questo metodo viene eseguito quando lanci `php artisan migrate`.
     * Definisce la struttura della nostra tabella.
     */
    public function up(): void
    {
        Schema::create('rider_categories', function (Blueprint $table) {
            $table->id(); // Colonna ID auto-incrementante
            $table->string('name')->unique(); // Colonna per il nome della categoria (es. 'GC', 'Velocista')
            
            // QUESTA È LA RIGA CHE ABBIAMO AGGIUNTO
            // Crea una colonna di tipo stringa per la descrizione.
            // ->nullable() la rende opzionale, quindi non darà errore se non la forniamo.
            $table->string('description')->nullable(); 
            
            $table->timestamps(); // Colonne 'created_at' e 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     *
     * Questo metodo viene eseguito quando lanci `php artisan migrate:rollback`.
     * Dice a Laravel come cancellare la tabella.
     */
    public function down(): void
    {
        Schema::dropIfExists('rider_categories');
    }
};
