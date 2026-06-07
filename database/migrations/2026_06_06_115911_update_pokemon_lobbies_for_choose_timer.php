<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pokemon_lobbies')) {
            Schema::create('pokemon_lobbies', function (Blueprint $table) {
                $table->id();

                $table->foreignId('player_one_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('player_two_id')->nullable()->constrained('users')->nullOnDelete();

                $table->string('player_one_pokemon')->nullable();
                $table->string('player_two_pokemon')->nullable();

                $table->integer('player_one_power')->default(0);
                $table->integer('player_two_power')->default(0);

                $table->json('player_one_data')->nullable();
                $table->json('player_two_data')->nullable();

                $table->decimal('bet_amount', 15, 2)->default(0);
                $table->decimal('pot_amount', 15, 2)->default(0);
                $table->decimal('payout_amount', 15, 2)->default(0);

                $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();

                $table->string('status')->default('waiting'); // waiting, choosing, finished, cancelled
                $table->timestamp('choice_deadline')->nullable();
                $table->timestamp('finished_at')->nullable();

                $table->timestamps();
            });

            return;
        }

        Schema::table('pokemon_lobbies', function (Blueprint $table) {
            if (! Schema::hasColumn('pokemon_lobbies', 'player_two_id')) {
                $table->foreignId('player_two_id')->nullable()->after('player_one_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_one_pokemon')) {
                $table->string('player_one_pokemon')->nullable()->after('player_two_id');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_two_pokemon')) {
                $table->string('player_two_pokemon')->nullable()->after('player_one_pokemon');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_one_power')) {
                $table->integer('player_one_power')->default(0)->after('player_two_pokemon');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_two_power')) {
                $table->integer('player_two_power')->default(0)->after('player_one_power');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_one_data')) {
                $table->json('player_one_data')->nullable()->after('player_two_power');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'player_two_data')) {
                $table->json('player_two_data')->nullable()->after('player_one_data');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'bet_amount')) {
                $table->decimal('bet_amount', 15, 2)->default(0)->after('player_two_data');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'pot_amount')) {
                $table->decimal('pot_amount', 15, 2)->default(0)->after('bet_amount');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'payout_amount')) {
                $table->decimal('payout_amount', 15, 2)->default(0)->after('pot_amount');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'winner_id')) {
                $table->foreignId('winner_id')->nullable()->after('payout_amount')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'status')) {
                $table->string('status')->default('waiting')->after('winner_id');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'choice_deadline')) {
                $table->timestamp('choice_deadline')->nullable()->after('status');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'finished_at')) {
                $table->timestamp('finished_at')->nullable()->after('choice_deadline');
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
            if (Schema::hasColumn('pokemon_lobbies', 'choice_deadline')) {
                $table->dropColumn('choice_deadline');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'finished_at')) {
                $table->dropColumn('finished_at');
            }
        });
    }
};