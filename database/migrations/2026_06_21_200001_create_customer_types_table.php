<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('customer_types')) { return; }
        Schema::create('customer_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('discount_percentage')->default(0); // 0–100
            $table->string('cost_bearer')->default('business');            // business | barber
            $table->string('color')->default('amber');                     // amber|purple|blue|green|red|gray
            $table->boolean('affects_loyalty')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('customer_types'); }
};
