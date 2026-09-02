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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('order_public_id');
            $table->string('status');
            $table->decimal('amount', 10, 2);
            $table->string('currency');
            $table->dateTime('event_created_at');
            $table->timestamps();

            $table->foreign('order_public_id')->references('public_id')->on('orders')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
