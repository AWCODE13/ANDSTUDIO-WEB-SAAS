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
    Schema::create('tenants', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('email')->unique();
        $table->string('phone', 20)->nullable();
        $table->text('address')->nullable();
        $table->string('logo_path')->nullable();
        $table->enum('subscription_plan', ['free', 'pro'])->default('free');
        $table->enum('subscription_status', ['active', 'past_due', 'canceled'])->default('active');
        $table->time('operational_start')->default('09:00:00');
        $table->time('operational_end')->default('21:00:00');
        $table->text('booking_policy')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
