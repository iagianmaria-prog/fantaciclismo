<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // In ..._create_auctions_table.php

public function up(): void
{
    Schema::create('auctions', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Es. "Asta Iniziale 2026"
        $table->string('type'); // "initial" o "repair"
        $table->dateTime('starts_at'); // Data e ora di inizio
        $table->dateTime('ends_at');   // Data e ora di fine
        $table->string('status')->default('scheduled'); // scheduled, open, closed
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
