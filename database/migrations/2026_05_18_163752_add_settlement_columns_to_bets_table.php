<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('amount');
            $table->decimal('win_amount', 12, 2)->default(0)->after('status');
            $table->decimal('payout_amount', 12, 2)->default(0)->after('win_amount');
            $table->foreignId('declaration_id')->nullable()->after('payout_amount')->constrained('game_declarations')->nullOnDelete();
            $table->timestamp('settled_at')->nullable()->after('declaration_id');
        });
    }

    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('declaration_id');
            $table->dropColumn([
                'status',
                'win_amount',
                'payout_amount',
                'settled_at',
            ]);
        });
    }
};