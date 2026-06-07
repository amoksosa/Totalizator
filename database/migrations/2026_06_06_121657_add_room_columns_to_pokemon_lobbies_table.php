<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pokemon_lobbies', function (Blueprint $table) {
            if (! Schema::hasColumn('pokemon_lobbies', 'choice_deadline')) {
                $table->timestamp('choice_deadline')->nullable()->after('status');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'finished_at')) {
                $table->timestamp('finished_at')->nullable()->after('choice_deadline');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('finished_at');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'round_number')) {
                $table->integer('round_number')->default(1)->after('closed_at');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_one_score')) {
                $table->integer('player_one_score')->default(0)->after('round_number');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_two_score')) {
                $table->integer('player_two_score')->default(0)->after('player_one_score');
            }
        });

        if (Schema::hasColumn('pokemon_lobbies', 'player_one_pokemon')) {
            DB::statement("ALTER TABLE pokemon_lobbies MODIFY player_one_pokemon VARCHAR(255) NULL");
        }

        if (Schema::hasColumn('pokemon_lobbies', 'player_two_pokemon')) {
            DB::statement("ALTER TABLE pokemon_lobbies MODIFY player_two_pokemon VARCHAR(255) NULL");
        }
    }

    public function down(): void
    {
        Schema::table('pokemon_lobbies', function (Blueprint $table) {
            if (Schema::hasColumn('pokemon_lobbies', 'player_two_score')) {
                $table->dropColumn('player_two_score');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'player_one_score')) {
                $table->dropColumn('player_one_score');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'round_number')) {
                $table->dropColumn('round_number');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'closed_at')) {
                $table->dropColumn('closed_at');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'finished_at')) {
                $table->dropColumn('finished_at');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'choice_deadline')) {
                $table->dropColumn('choice_deadline');
            }
        });
    }
};