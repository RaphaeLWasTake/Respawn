<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('game');
            $table->string('package');
            $table->string('player_id');
            $table->unsignedInteger('amount');
            $table->string('payment_method');
            $table->string('status')->default('Menunggu pembayaran');
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->text('reply')->nullable();
            $table->string('status')->default('Menunggu balasan');
            $table->timestamps();
        });

        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_key', 64);
            $table->date('visited_on');
            $table->timestamps();
            $table->unique(['visitor_key', 'visited_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('orders');
    }
};
