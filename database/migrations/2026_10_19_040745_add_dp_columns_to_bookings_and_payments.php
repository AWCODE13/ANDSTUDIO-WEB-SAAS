<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // bookings.total_price - idempotent (aman dijalankan ulang)
        if (! Schema::hasColumn('bookings', 'total_price')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->integer('total_price')->default(0)->after('total_amount');
            });
        }

        // payments.payment_type - idempotent
        if (! Schema::hasColumn('payments', 'payment_type')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('payment_type', 20)->default('dp')->after('amount');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'total_price')) {
                $table->dropColumn('total_price');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });
    }
};
