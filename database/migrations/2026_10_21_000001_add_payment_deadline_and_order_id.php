<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('payment_deadline')->nullable()->after('booking_status');
        });

        // Backfill: deadline lama = created_at + 15 menit
        DB::statement(
            "UPDATE bookings SET payment_deadline = DATE_ADD(created_at, INTERVAL 15 MINUTE) WHERE payment_deadline IS NULL"
        );

        Schema::table('payments', function (Blueprint $table) {
            $table->string('midtrans_order_id', 64)->nullable()->unique()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['midtrans_order_id']);
            $table->dropColumn('midtrans_order_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_deadline');
        });
    }
};
