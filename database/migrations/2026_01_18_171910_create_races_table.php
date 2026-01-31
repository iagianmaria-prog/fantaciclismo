<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // In ..._create_races_table.php

public function up(): void
{
    Schema::create('races', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->date('date');
        $table->string('type'); // Es. "Corsa di un giorno", "Corsa a tappe"
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
