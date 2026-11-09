<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Apakah kolom logo_path dibuat oleh migrasi ini.
     * (Sudah ada di migrasi awal, tapi dijaga untuk DB yang dibuat versi lama.)
     */
    private bool $addedLogo = false;

    public function up(): void
    {
        $this->addedLogo = ! Schema::hasColumn('tenants', 'logo_path');

        Schema::table('tenants', function (Blueprint $table) {
            if ($this->addedLogo) {
                $table->string('logo_path')->nullable();
            }

            $table->string('instagram_url')->nullable();
            $table->decimal('rating_score', 3, 2)->default(4.90);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['instagram_url', 'rating_score']);

            if ($this->addedLogo) {
                $table->dropColumn('logo_path');
            }
        });
    }
};
