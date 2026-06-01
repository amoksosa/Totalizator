<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->string('conversion_status')
                ->default('pending')
                ->after('total_commission_amount');

            $table->timestamp('converted_at')
                ->nullable()
                ->after('conversion_status');
        });
    }

    public function down(): void
    {
        Schema::table('agent_commissions', function (Blueprint $table) {
            $table->dropColumn([
                'conversion_status',
                'converted_at',
            ]);
        });
    }
};