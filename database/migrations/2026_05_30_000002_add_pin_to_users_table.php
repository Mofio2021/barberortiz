<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // PIN hasheado de 4 dígitos — opcional, para acceso rápido desde móvil.
            // Null = usuario solo usa contraseña larga.
            if (! Schema::hasColumn('users', 'pin')) {
                $table->string('pin')->nullable()->after('photo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pin')) {
                $table->dropColumn('pin');
            }
        });
    }
};
