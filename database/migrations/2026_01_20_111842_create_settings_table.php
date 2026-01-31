<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Il nome della classe corrisponde al nome del file
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Questo metodo crea la tabella 'settings' nel database.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id(); // Colonna ID
            
            // Colonna 'key' per il nome dell'impostazione (es. 'initial_budget').
            // ->unique() assicura che non ci siano due impostazioni con lo stesso nome.
            $table->string('key')->unique(); 
            
            // Colonna 'value' per il valore dell'impostazione.
            // ->nullable() permette che un'impostazione possa non avere un valore.
            $table->text('value')->nullable(); 
            
            $table->timestamps(); // Colonne 'created_at' e 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     *
     * Questo metodo cancella la tabella se eseguiamo un rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
