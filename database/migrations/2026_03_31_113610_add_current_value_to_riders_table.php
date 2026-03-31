<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            // Valore corrente (può essere modificato manualmente)
            $table->integer('current_value')->nullable()->after('initial_value');
            // Prezzo di acquisto effettivo (pagato dalla squadra)
            $table->integer('purchase_price')->nullable()->after('current_value');
        });

        // Inizializza current_value con initial_value per i corridori esistenti
        DB::statement('UPDATE riders SET current_value = initial_value WHERE current_value IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(['current_value', 'purchase_price']);
        });
    }
};
