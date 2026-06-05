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
        Schema::table('bets', function (Blueprint $table) {
            if (! Schema::hasColumn('bets', 'game_round_id')) {
                $table->foreignId('game_round_id')
                    ->nullable()
                    ->after('game_event_id')
                    ->constrained('game_rounds')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('bets', 'requested_amount')) {
                $table->decimal('requested_amount', 15, 2)
                    ->default(0)
                    ->after('amount');
            }

            if (! Schema::hasColumn('bets', 'refunded_amount')) {
                $table->decimal('refunded_amount', 15, 2)
                    ->default(0)
                    ->after('requested_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            if (Schema::hasColumn('bets', 'game_round_id')) {
                $table->dropConstrainedForeignId('game_round_id');
            }

            if (Schema::hasColumn('bets', 'requested_amount')) {
                $table->dropColumn('requested_amount');
            }

            if (Schema::hasColumn('bets', 'refunded_amount')) {
                $table->dropColumn('refunded_amount');
            }
        });
    }
};