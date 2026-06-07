<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

                $table->string('player_one_pokemon');
                $table->string('player_two_pokemon')->nullable();

                $table->integer('player_one_power')->default(0);
                $table->integer('player_two_power')->default(0);

                $table->json('player_one_data')->nullable();
                $table->json('player_two_data')->nullable();

                $table->decimal('bet_amount', 15, 2)->default(0);
                $table->decimal('pot_amount', 15, 2)->default(0);
                $table->decimal('payout_amount', 15, 2)->default(0);

                $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();

                $table->string('status')->default('waiting');

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon_lobbies');
    }
};