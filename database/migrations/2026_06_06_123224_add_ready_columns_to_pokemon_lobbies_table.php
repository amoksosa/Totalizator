<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pokemon_lobbies', function (Blueprint $table) {
            if (! Schema::hasColumn('pokemon_lobbies', 'player_one_ready')) {
                $table->boolean('player_one_ready')->default(false)->after('player_two_score');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_two_ready')) {
                $table->boolean('player_two_ready')->default(false)->after('player_one_ready');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pokemon_lobbies', function (Blueprint $table) {
            if (Schema::hasColumn('pokemon_lobbies', 'player_two_ready')) {
                $table->dropColumn('player_two_ready');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'player_one_ready')) {
                $table->dropColumn('player_one_ready');
            }
        });
    }
};