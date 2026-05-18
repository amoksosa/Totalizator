<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('declared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('winner');
            $table->string('round_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_declarations');
    }
};