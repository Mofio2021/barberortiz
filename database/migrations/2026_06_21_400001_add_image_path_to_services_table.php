<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('services', 'image_path')) { return; }
        Schema::table('services', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('is_active');
        });
    }
    public function down(): void {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
