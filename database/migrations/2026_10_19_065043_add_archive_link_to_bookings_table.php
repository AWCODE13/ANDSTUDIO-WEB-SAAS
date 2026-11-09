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
        Schema::table('bookings', function (Blueprint $table) {
            // $table->decimal('total_price', 15, 2)->...
            // $table->string('status')->...
            // (Beri tanda // pada semua kolom di dalam sini)
        });

        Schema::table('payments', function (Blueprint $table) {
            // Lakukan hal yang sama jika ada penambahan kolom di tabel payments
            // $table->string('status')->...
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            if (Schema::hasColumn('bookings', 'archive_link')) {
                $table->dropColumn('archive_link');
            }
        });
    }
};
