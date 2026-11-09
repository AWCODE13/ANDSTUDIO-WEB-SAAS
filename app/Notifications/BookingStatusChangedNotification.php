<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'confirmed' => 'Dikonfirmasi',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $label = $statusLabels[$this->newStatus] ?? ucfirst($this->newStatus);

        return [
            'type' => 'booking_status',
            'title' => 'Status Booking Berubah',
            'message' => 'Booking #' . $this->booking->id . ' status berubah menjadi: ' . $label,
            'booking_id' => $this->booking->id,
            'status' => $this->newStatus,
            'url' => '/app/bookings/' . $this->booking->id,
            'icon' => 'status',
        ];
    }
}
