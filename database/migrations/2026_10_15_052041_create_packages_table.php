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
    Schema::create('packages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
        $table->string('name');
        $table->string('category')->default('general');
        $table->text('description')->nullable();
        $table->decimal('price', 12, 2);
        $table->decimal('down_payment_min', 12, 2)->default(0);
        $table->unsignedInteger('duration_minutes')->default(60);
        $table->unsignedInteger('max_persons')->default(1);
        $table->boolean('is_active')->default(true);
        $table->timestamps();

        $table->index(['tenant_id', 'is_active']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
