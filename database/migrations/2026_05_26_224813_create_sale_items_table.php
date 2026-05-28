<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->enum('item_type', ['service','product']);
            $table->unsignedBigInteger('item_id');
            $table->string('item_name');
            $table->decimal('price_at_time', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sale_items'); }
};
