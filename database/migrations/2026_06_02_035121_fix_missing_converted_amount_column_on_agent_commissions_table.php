<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('agent_commissions', 'converted_amount')) {
            Schema::table('agent_commissions', function (Blueprint $table) {
                $table->decimal('converted_amount', 15, 2)
                    ->default(0)
                    ->after('commission_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('agent_commissions', 'converted_amount')) {
            Schema::table('agent_commissions', function (Blueprint $table) {
                $table->dropColumn('converted_amount');
            });
        }
    }
};