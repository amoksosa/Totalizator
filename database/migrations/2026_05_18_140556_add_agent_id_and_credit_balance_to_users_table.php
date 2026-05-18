<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'agent_id')) {
                $table->foreignId('agent_id')
                    ->nullable()
                    ->after('status')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'credit_balance')) {
                $table->decimal('credit_balance', 12, 2)
                    ->default(0)
                    ->after('agent_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'credit_balance')) {
                $table->dropColumn('credit_balance');
            }

            if (Schema::hasColumn('users', 'agent_id')) {
                $table->dropConstrainedForeignId('agent_id');
            }
        });
    }
};