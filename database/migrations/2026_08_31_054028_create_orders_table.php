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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('public_id')->unique();
            $table->string('sku');
            $table->decimal('amount', 10, 2);
            $table->string('currency');
            $table->string('status');
            $table->string('code')->nullable();
            $table->timestamps();

            $table->foreign('sku')->references('sku')->on('products')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
