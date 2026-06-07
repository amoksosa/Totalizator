<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pokemon_lobbies', function (Blueprint $table) {
            if (! Schema::hasColumn('pokemon_lobbies', 'gross_payout_amount')) {
                $table->decimal('gross_payout_amount', 12, 2)->default(0)->after('payout_amount');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'commission_amount')) {
                $table->decimal('commission_amount', 12, 2)->default(0)->after('gross_payout_amount');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'agent_commission_amount')) {
                $table->decimal('agent_commission_amount', 12, 2)->default(0)->after('commission_amount');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'company_commission_amount')) {
                $table->decimal('company_commission_amount', 12, 2)->default(0)->after('agent_commission_amount');
            }

            if (! Schema::hasColumn('pokemon_lobbies', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->after('company_commission_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pokemon_lobbies', function (Blueprint $table) {
            if (Schema::hasColumn('pokemon_lobbies', 'agent_id')) {
                $table->dropColumn('agent_id');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'company_commission_amount')) {
                $table->dropColumn('company_commission_amount');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'agent_commission_amount')) {
                $table->dropColumn('agent_commission_amount');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'commission_amount')) {
                $table->dropColumn('commission_amount');
            }

            if (Schema::hasColumn('pokemon_lobbies', 'gross_payout_amount')) {
                $table->dropColumn('gross_payout_amount');
            }
        });
    }
};