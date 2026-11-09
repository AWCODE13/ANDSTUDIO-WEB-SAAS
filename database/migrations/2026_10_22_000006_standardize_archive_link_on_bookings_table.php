<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Standarisasi link hasil foto (Google Drive) pada tabel bookings.
     *
     * Awalnya ada dua nama untuk satu data: `drive_archive_url` (dibuat di
     * migrasi awal) dan `archive_link` (dipakai controller + blade tapi
     * kolomnya tidak pernah dibuat karena migrasi 2026_10_19_065043 kosong).
     *
     * Disatukan ke `archive_link`, data lama dipindahkan, lalu kolom lama dibuang.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('bookings', 'archive_link')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->text('archive_link')->nullable()->after('drive_archive_url');
            });
        }

        if (Schema::hasColumn('bookings', 'drive_archive_url')) {
            DB::table('bookings')
                ->whereNotNull('drive_archive_url')
                ->update(['archive_link' => DB::raw('drive_archive_url')]);

            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('drive_archive_url');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('bookings', 'drive_archive_url')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->text('drive_archive_url')->nullable();
            });
        }

        DB::table('bookings')
            ->whereNotNull('archive_link')
            ->update(['drive_archive_url' => DB::raw('archive_link')]);

        if (Schema::hasColumn('bookings', 'archive_link')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('archive_link');
            });
        }
    }
};
