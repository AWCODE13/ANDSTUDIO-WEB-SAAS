<?php

namespace App\Notifications;

use App\Models\StudioReview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification
{
    use Queueable;

    public function __construct(public StudioReview $review)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_review',
            'title' => 'Ulasan Baru (' . $this->review->rating . '★)',
            'message' => ($this->review->customer_name ?? 'Customer') . ': "' . \Illuminate\Support\Str::limit($this->review->comment ?? '', 60) . '"',
            'review_id' => $this->review->id,
            'rating' => $this->review->rating,
            'url' => '/app/ulasan',
            'icon' => 'star',
        ];
    }
}
