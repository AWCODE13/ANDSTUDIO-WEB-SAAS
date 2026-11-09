<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom moderasi untuk ulasan pelanggan.
     * Ulasan yang disembunyikan tidak tampil di profil publik dan tidak
     * ikut dihitung dalam rating studio.
     */
    public function up(): void
    {
        Schema::table('studio_reviews', function (Blueprint $table) {
            $table->enum('status', ['visible', 'hidden'])->default('visible')->after('rating');
            $table->timestamp('hidden_at')->nullable()->after('status');
            $table->foreignId('hidden_by')->nullable()->after('hidden_at')->constrained('users')->nullOnDelete();
            $table->text('hidden_reason')->nullable()->after('hidden_by');

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('studio_reviews', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropColumn(['status', 'hidden_at', 'hidden_by', 'hidden_reason']);
        });
    }
};
