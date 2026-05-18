<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agent_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('player_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('bet_id')
                ->constrained('bets')
                ->cascadeOnDelete();

            $table->decimal('bet_amount', 12, 2);
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->decimal('commission_amount', 12, 2);

            $table->string('side');
            $table->string('odds');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commissions');
    }
};