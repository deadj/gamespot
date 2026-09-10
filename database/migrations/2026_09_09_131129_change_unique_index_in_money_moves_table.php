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
        Schema::table('money_moves', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'type']);
            $table->unique(['order_id', 'order_item_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('money_moves', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'order_item_id', 'type']);
            $table->unique(['order_id', 'type']);
        });
    }
};
