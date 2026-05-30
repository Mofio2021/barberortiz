<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // FK al barbero que realizó el servicio
            if (! Schema::hasColumn('sales', 'staff_id')) {
                $table->foreignId('staff_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('staff')
                    ->nullOnDelete();
            }

            // FK al cajero que registró el cobro
            if (! Schema::hasColumn('sales', 'cashier_id')) {
                $table->foreignId('cashier_id')
                    ->nullable()
                    ->after('staff_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('sales', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0.00)->after('cashier_id');
            }

            if (! Schema::hasColumn('sales', 'total')) {
                $table->decimal('total', 10, 2)->default(0.00)->after('subtotal');
            }

            if (! Schema::hasColumn('sales', 'total_commission')) {
                $table->decimal('total_commission', 10, 2)->default(0.00)->after('total');
            }

            if (! Schema::hasColumn('sales', 'amount_paid')) {
                $table->decimal('amount_paid', 10, 2)->default(0.00)->after('total_commission');
            }

            if (! Schema::hasColumn('sales', 'change_given')) {
                $table->decimal('change_given', 10, 2)->default(0.00)->after('amount_paid');
            }
        });

        // Migra datos históricos de columnas antiguas a las nuevas
        // (seguro aunque no haya registros aún)
        DB::statement("
            UPDATE sales SET
                subtotal   = COALESCE(total_amount, 0),
                total      = COALESCE(final_amount, 0),
                cashier_id = COALESCE(cashier_id, user_id)
            WHERE subtotal = 0 OR total = 0
        ");
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $toDrop = [];

            foreach (['staff_id', 'cashier_id', 'subtotal', 'total',
                      'total_commission', 'amount_paid', 'change_given'] as $col) {
                if (Schema::hasColumn('sales', $col)) {
                    $toDrop[] = $col;
                }
            }

            if (! empty($toDrop)) {
                // Elimina FKs primero para evitar error de constraint
                foreach (['staff_id', 'cashier_id'] as $fk) {
                    if (in_array($fk, $toDrop)) {
                        $table->dropForeign(['staff_id']);
                        $table->dropForeign(['cashier_id']);
                        break;
                    }
                }
                $table->dropColumn($toDrop);
            }
        });
    }
};
