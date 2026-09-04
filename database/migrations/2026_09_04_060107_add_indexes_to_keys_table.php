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
        Schema::table('keys', function (Blueprint $table) {
            $table->index(['sku', 'request_id']);
            $table->foreign('sku')->references('sku')->on('products')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keys', function (Blueprint $table) {
            $table->dropForeign(['sku']);
            $table->dropIndex(['sku', 'request_id']);
        });
    }
};
