<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
            $table->timestamp('next_payment_at')->nullable()->after('trial_ends_at');
            $table->timestamp('plan_expires_at')->nullable()->after('next_payment_at');
            $table->boolean('is_partner')->default(false)->after('plan_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['trial_ends_at', 'next_payment_at', 'plan_expires_at', 'is_partner']);
        });
    }
};
