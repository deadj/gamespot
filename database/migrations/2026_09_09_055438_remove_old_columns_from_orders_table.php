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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['sku']);

            $table->dropColumn('sku');
            $table->dropColumn('amount');
            $table->dropColumn('currency');
            $table->dropColumn('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('sku');
            $table->decimal('amount', 10, 2);
            $table->string('currency');      
            $table->string('code');     
           
            $table->foreign('sku')->references('sku')->on('products')->cascadeOnUpdate()->restrictOnDelete();
        });
    }
};
