<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('role')->default('Barbero');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->enum('payment_type', ['commission','salary','hybrid'])->default('commission');
            $table->enum('commission_type', ['percentage','fixed'])->default('percentage');
            $table->decimal('commission_value', 10, 2)->default(50.00);
            $table->decimal('base_salary', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('staff'); }
};
