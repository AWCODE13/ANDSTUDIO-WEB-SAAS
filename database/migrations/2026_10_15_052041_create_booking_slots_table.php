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
    Schema::create('booking_slots', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
        $table->date('date');
        $table->time('start_time');
        $table->time('end_time');
        $table->enum('status', ['available', 'reserved', 'blocked'])->default('available');
        $table->timestamps();

        $table->unique(['tenant_id', 'date', 'start_time'], 'uniq_tenant_slot');
        $table->index(['tenant_id', 'date', 'status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_slots');
    }
};
