<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('sales', 'cost_bearer')) { return; }
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('customer_type_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('customer_types')
                ->nullOnDelete();
            $table->string('cost_bearer')->nullable()->after('customer_type_id');
        });
    }
    public function down(): void {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['customer_type_id']);
            $table->dropColumn(['customer_type_id', 'cost_bearer']);
        });
    }
};
