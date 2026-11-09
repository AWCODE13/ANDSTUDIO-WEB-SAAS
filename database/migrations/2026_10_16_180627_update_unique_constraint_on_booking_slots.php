<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_slots', function (Blueprint $table) {
            // 1. Hapus aturan unik yang lama
            $table->dropUnique('uniq_tenant_slot');
            
            // 2. Buat aturan unik baru yang menyertakan package_id
            $table->unique(['tenant_id', 'package_id', 'date', 'start_time'], 'uniq_tenant_package_slot');
        });
    }

    public function down(): void
    {
        Schema::table('booking_slots', function (Blueprint $table) {
            $table->dropUnique('uniq_tenant_package_slot');
            $table->unique(['tenant_id', 'date', 'start_time'], 'uniq_tenant_slot');
        });
    }
};