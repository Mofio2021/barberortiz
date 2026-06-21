<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('commission_payments')) {
            Schema::create('commission_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('gross_amount', 10, 2)->default(0);
                $table->decimal('deductions', 10, 2)->default(0);
                $table->decimal('net_amount', 10, 2)->default(0);
                $table->enum('status', ['pending', 'paid'])->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Agregar FK solo si aún no existe (compatible con Laravel 10 y 11)
        $hasFk = \DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'staff_consumptions'
              AND COLUMN_NAME = 'commission_payment_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (empty($hasFk)) {
            Schema::table('staff_consumptions', function (Blueprint $table) {
                $table->foreign('commission_payment_id')
                      ->references('id')->on('commission_payments')
                      ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_consumptions', function (Blueprint $table) {
            $table->dropForeign(['commission_payment_id']);
        });
        Schema::dropIfExists('commission_payments');
    }
};
