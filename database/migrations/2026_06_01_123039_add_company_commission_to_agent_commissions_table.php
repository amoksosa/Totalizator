<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->decimal('company_commission_rate', 8, 2)->default(2.00)->after('commission_amount');
            $table->decimal('company_commission_amount', 15, 2)->default(0)->after('company_commission_rate');
            $table->decimal('total_commission_rate', 8, 2)->default(5.00)->after('company_commission_amount');
            $table->decimal('total_commission_amount', 15, 2)->default(0)->after('total_commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->dropColumn([
                'company_commission_rate',
                'company_commission_amount',
                'total_commission_rate',
                'total_commission_amount',
            ]);
        });
    }
};