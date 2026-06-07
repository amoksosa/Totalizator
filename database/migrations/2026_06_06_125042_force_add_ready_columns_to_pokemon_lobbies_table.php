<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pokemon_lobbies')) {
            if (! Schema::hasColumn('pokemon_lobbies', 'player_one_ready')) {
                DB::statement("ALTER TABLE pokemon_lobbies ADD player_one_ready TINYINT(1) NOT NULL DEFAULT 0");
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_two_ready')) {
                DB::statement("ALTER TABLE pokemon_lobbies ADD player_two_ready TINYINT(1) NOT NULL DEFAULT 0");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pokemon_lobbies')) {
            if (Schema::hasColumn('pokemon_lobbies', 'player_two_ready')) {
                DB::statement("ALTER TABLE pokemon_lobbies DROP COLUMN player_two_ready");
            }

            if (Schema::hasColumn('pokemon_lobbies', 'player_one_ready')) {
                DB::statement("ALTER TABLE pokemon_lobbies DROP COLUMN player_one_ready");
            }
        }
    }
};