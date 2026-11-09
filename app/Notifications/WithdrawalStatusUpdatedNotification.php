<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WithdrawalStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Withdrawal $withdrawal
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isCompleted = $this->withdrawal->status === 'completed';
        $formatted = 'Rp ' . number_format($this->withdrawal->amount, 0, ',', '.');

        $title = $isCompleted ? 'Pencairan Dana Berhasil' : 'Pencairan Dana Ditolak';
        $message = $isCompleted
            ? "Penarikan saldo sebesar {$formatted} telah ditransfer ke rekening {$this->withdrawal->bank_name}."
            : "Penarikan saldo sebesar {$formatted} ditolak. Alasan: " . ($this->withdrawal->rejection_reason ?: '-');

        return [
            'type'          => 'withdrawal_status',
            'title'         => $title,
            'message'       => $message,
            'withdrawal_id' => $this->withdrawal->id,
            'status'        => $this->withdrawal->status,
            'url'           => route('tenant.withdrawals.index'),
            'icon'          => $isCompleted ? 'shield' : 'bell',
        ];
    }
}
