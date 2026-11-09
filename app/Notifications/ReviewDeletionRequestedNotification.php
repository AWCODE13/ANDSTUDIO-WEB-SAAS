<?php

namespace App\Notifications;

use App\Models\ReviewDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewDeletionRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public ReviewDeletionRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review_deletion_request',
            'title' => 'Permintaan Hapus Review',
            'message' => 'Studio "' . ($this->request->tenant->name ?? 'Studio') . '" meminta review dihapus.',
            'request_id' => $this->request->id,
            'url' => '/superadmin/permintaan-hapus',
            'icon' => 'shield',
        ];
    }
}
