<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sales_reports', function (Blueprint $table) {
            $table->id();

            $table->string('source_game'); 
            // totalizator, pokemon

            $table->unsignedBigInteger('source_id')->nullable();
            // pokemon_lobby_id / totalizator round id

            $table->string('event_name')->nullable();
            // Pokemon Battle Room / Totalizator Event Name

            $table->string('round_label')->nullable();

            $table->unsignedBigInteger('winner_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();

            $table->decimal('total_bet_amount', 12, 2)->default(0);
            $table->decimal('gross_payout_amount', 12, 2)->default(0);
            $table->decimal('net_payout_amount', 12, 2)->default(0);

            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('agent_commission_amount', 12, 2)->default(0);
            $table->decimal('company_commission_amount', 12, 2)->default(0);

            $table->string('status')->default('settled');
            // settled, draw, cancelled, refunded

            $table->timestamp('settled_at')->nullable();

            $table->timestamps();

            $table->index(['source_game', 'settled_at']);
            $table->index(['agent_id', 'source_game']);
            $table->index('event_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sales_reports');
    }
};