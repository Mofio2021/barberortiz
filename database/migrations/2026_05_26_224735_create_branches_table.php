<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->default('Santa Cruz');
            $table->boolean('is_main')->default(false);
            $table->boolean('is_active')->default(true);
            $table->time('open_time')->default('08:00:00');
            $table->time('close_time')->default('20:00:00');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('branches'); }
};
