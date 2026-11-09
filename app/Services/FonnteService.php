<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    /**
     * Kirim pesan WhatsApp lewat Fonnte.
     *
     * @return bool true kalau terkirim, false kalau token kosong / nomor
     *              tidak valid / layanan Fonnte gagal.
     */
    public static function send(string $phone, string $message): bool
    {
        $token = (string) env('FONNTE_TOKEN');

        if ($token === '') {
            return false;
        }

        $target = self::normalizePhone($phone);

        if ($target === '') {
            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])->post(
                'https://api.fonnte.com/send',
                [
                    'target'  => $target,
                    'message' => $message,
                ]
            );

            $data = $response->json();

            return (bool) ($data['status'] ?? false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Normalisasi nomor Indonesia ke format Fonnte (62xxx).
     * "0812-3456-7890" -> "6281234567890"
     */
    public static function normalizePhone(string $phone): string
    {
        $digits = (string) preg_replace('/\D/', '', $phone);

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return $digits;
    }
}
