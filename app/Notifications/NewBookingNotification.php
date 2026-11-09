<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_booking',
            'title' => 'Booking Baru',
            'message' => ($this->booking->customer_name ?? 'Customer') . ' memesan ' . ($this->booking->package->name ?? 'Paket Foto'),
            'amount' => (float) $this->booking->total_amount,
            'booking_id' => $this->booking->id,
            'url' => '/app/bookings/' . $this->booking->id,
            'icon' => 'calendar',
        ];
    }
}
