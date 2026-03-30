<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->integer('contract_years')->nullable()->after('initial_value');
            $table->integer('contract_remaining_years')->nullable()->after('contract_years');
            $table->date('contract_start_date')->nullable()->after('contract_remaining_years');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(['contract_years', 'contract_remaining_years', 'contract_start_date']);
        });
    }
};
