<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('customers', 'ci')) { return; }
        Schema::table('customers', function (Blueprint $table) {
            $table->string('ci')->nullable()->after('phone'); // cédula de identidad
        });
    }
    public function down(): void {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('ci');
        });
    }
};
