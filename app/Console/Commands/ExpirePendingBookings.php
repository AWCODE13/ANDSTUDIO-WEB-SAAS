<?php

namespace App\Console\Commands;

use App\Services\BookingExpiryService;
use Illuminate\Console\Command;

class ExpirePendingBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan booking yang lewat batas waktu pembayaran dan kembalikan slot ke available';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = BookingExpiryService::releaseExpired();

        if ($count > 0) {
            $this->info("Total {$count} booking dibatalkan otomatis (slot dikembalikan).");
        } else {
            $this->info('Tidak ada booking yang perlu dibatalkan.');
        }

        return Command::SUCCESS;
    }
}
