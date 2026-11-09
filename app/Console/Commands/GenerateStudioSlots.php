<?php

namespace App\Console\Commands;

use App\Models\BookingSlot;
use App\Models\Package;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateStudioSlots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =
        'studio:generate-slots {tenant? : Slug tenant (kosong = semua tenant)} {--days=7 : Berapa hari ke depan} {--from= : Tanggal mulai (default hari ini)} {--times=09:00,10:00,11:00,13:00,14:00,15:00,16:00 : Jam sesi} {--interval=60 : Durasi slot (menit)} {--replace : Hapus slot yang ada di rentang tanggal tersebut}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description =
        'Generate slot jadwal untuk semua paket pada sebuah studio (multi-hari, aman dari duplikat)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenants = Tenant::query()
            ->when($this->argument('tenant'), fn ($q) => $q->where('slug', $this->argument('tenant')))
            ->get();

        if ($tenants->isEmpty()) {
            $this->error('Tenant tidak ditemukan.');
            return Command::FAILURE;
        }

        $days = max(1, (int) $this->option('days'));
        $interval = max(15, (int) $this->option('interval'));
        $from = $this->option('from')
            ? Carbon::parse($this->option('from'))->startOfDay()
            : Carbon::today();
        $times = collect(explode(',', $this->option('times')))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values();

        if ($times->isEmpty()) {
            $this->error('Minimal satu jam sesi harus diisi (--times).');
            return Command::FAILURE;
        }

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($tenants as $tenant) {
            $packages = Package::where('tenant_id', $tenant->id)->where('is_active', true)->get();

            if ($packages->isEmpty()) {
                $this->warn("  {$tenant->name}: tidak ada paket aktif, dilewati.");
                continue;
            }

            if ($this->option('replace')) {
                $deleted = BookingSlot::where('tenant_id', $tenant->id)
                    ->whereBetween('date', [
                        $from->toDateString(),
                        $from->copy()->addDays($days - 1)->toDateString(),
                    ])
                    ->delete();
                $this->line("  {$tenant->name}: {$deleted} slot lama dihapus (--replace).");
            }

            $created = 0;
            $skipped = 0;

            foreach ($packages as $package) {
                for ($d = 0; $d < $days; $d++) {
                    $date = $from->copy()->addDays($d);

                    foreach ($times as $time) {
                        $start = Carbon::parse($date->toDateString() . ' ' . $time);
                        $end = $start->copy()->addMinutes($interval);

                        $exists = BookingSlot::where('tenant_id', $tenant->id)
                            ->where('package_id', $package->id)
                            ->where('date', $date->toDateString())
                            ->where('start_time', $start->format('H:i:s'))
                            ->exists();

                        if ($exists) {
                            $skipped++;
                            continue;
                        }

                        BookingSlot::create([
                            'tenant_id'  => $tenant->id,
                            'package_id' => $package->id,
                            'date'       => $date->toDateString(),
                            'start_time' => $start->format('H:i:s'),
                            'end_time'   => $end->format('H:i:s'),
                            'status'     => 'available',
                        ]);
                        $created++;
                    }
                }
            }

            $this->info(
                "  {$tenant->name}: {$created} slot dibuat, {$skipped} duplikat dilewati."
            );
            $totalCreated += $created;
            $totalSkipped += $skipped;
        }

        $this->info("Selesai: {$totalCreated} slot dibuat, {$totalSkipped} dilewati.");

        return Command::SUCCESS;
    }
}
