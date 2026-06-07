<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        });
    }

    public function down(): void
    {
        Schema::table('pokemon_lobbies', function (Blueprint $table) {
            if (Schema::hasColumn('pokemon_lobbies', 'finished_at')) {
                $table->dropColumn('finished_at');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'choice_deadline')) {
                $table->dropColumn('choice_deadline');
            }
        });
    }
};