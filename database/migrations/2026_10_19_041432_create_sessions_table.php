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
        // Cek apakah kolom belum ada, baru buat
        if (!Schema::hasColumn('bookings', 'total_price')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->integer('total_price')->default(0);
            });
        }

        if (!Schema::hasColumn('payments', 'payment_type')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('payment_type', 20)->default('dp');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
