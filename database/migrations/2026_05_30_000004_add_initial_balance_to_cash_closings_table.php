<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_closings', 'initial_balance')) {
                $table->decimal('initial_balance', 10, 2)->default(0)->after('closing_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            if (Schema::hasColumn('cash_closings', 'initial_balance')) {
                $table->dropColumn('initial_balance');
            }
        });
    }
};
