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
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
        $table->foreignId('package_id')->constrained('packages')->restrictOnDelete();
        $table->foreignId('booking_slot_id')->nullable()->constrained('booking_slots')->nullOnDelete();
        $table->foreignId('assigned_photographer_id')->nullable()->constrained('users')->nullOnDelete();

        $table->string('booking_code', 20)->unique();
        $table->string('customer_name');
        $table->string('customer_email');
        $table->string('customer_phone', 20);
        $table->date('booking_date');
        $table->time('start_time');
        $table->time('end_time');

        $table->decimal('total_amount', 12, 2);
        $table->decimal('paid_amount', 12, 2)->default(0);
        $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'refunded'])->default('unpaid');
        $table->enum('booking_status', ['pending', 'confirmed', 'in_progress', 'completed', 'canceled'])->default('pending');

        $table->text('drive_archive_url')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();

        $table->index(['tenant_id', 'booking_date']);
        $table->index(['tenant_id', 'booking_status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
