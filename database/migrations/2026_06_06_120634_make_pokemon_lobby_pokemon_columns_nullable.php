<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pokemon_lobbies')) {
            DB::statement("ALTER TABLE pokemon_lobbies MODIFY player_one_pokemon VARCHAR(255) NULL");
            DB::statement("ALTER TABLE pokemon_lobbies MODIFY player_two_pokemon VARCHAR(255) NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pokemon_lobbies')) {
            DB::statement("ALTER TABLE pokemon_lobbies MODIFY player_one_pokemon VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE pokemon_lobbies MODIFY player_two_pokemon VARCHAR(255) NULL");
        }
    }
};