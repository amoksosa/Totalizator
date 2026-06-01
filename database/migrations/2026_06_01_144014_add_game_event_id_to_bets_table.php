<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->foreignId('game_event_id')
                ->nullable()
                ->after('id')
                ->constrained('game_events')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropForeign(['game_event_id']);
            $table->dropColumn('game_event_id');
        });
    }
};