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
        Schema::table('trades', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_trade_id')->nullable()->after('status');
            $table->foreign('parent_trade_id')->references('id')->on('trades')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropForeign(['parent_trade_id']);
            $table->dropColumn('parent_trade_id');
        });
    }
};
