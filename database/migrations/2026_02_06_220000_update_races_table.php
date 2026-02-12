<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->integer('lineup_size')->default(9); // Numero corridori schierabili
            $table->dateTime('lineup_deadline')->nullable(); // Deadline per schierare la formazione
            $table->enum('status', ['upcoming', 'lineup_open', 'in_progress', 'completed'])->default('upcoming');
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn(['lineup_size', 'lineup_deadline', 'status', 'description']);
        });
    }
};
