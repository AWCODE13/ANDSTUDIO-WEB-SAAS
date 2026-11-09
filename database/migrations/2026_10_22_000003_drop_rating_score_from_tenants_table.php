<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buang kolom rating dummy. Rating studio sekarang dihitung dari
     * ulasan pelanggan (tabel studio_reviews).
     */
    public function up(): void
    {
        if (Schema::hasColumn('tenants', 'rating_score')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('rating_score');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tenants', 'rating_score')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->decimal('rating_score', 3, 2)->default(4.90);
            });
        }
    }
};
