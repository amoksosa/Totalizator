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
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_event_id')
                ->constrained('game_events')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('round_code')->nullable();

            // open = players can bet
            // closed = betting is closed, waiting for declare winner
            // settled = winner already declared
            $table->string('status')->default('open');

            // MERON, WALA, DRAW
            $table->string('winner')->nullable();

            $table->timestamp('betting_closed_at')->nullable();
            $table->timestamp('settled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};