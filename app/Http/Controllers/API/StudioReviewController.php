<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\StudioReview;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudioReviewController extends Controller
{
    /**
     * Simpan ulasan pelanggan.
     *
     * Identitas booking boleh lewat booking_code ATAU email/WhatsApp,
     * tapi tetap harus terbukti: kode wajib dicocokkan dengan email/HP,
     * atau email/HP harus cocok dengan booking yang sudah selesai.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_code' => 'nullable|string|max:50',
            'email'        => 'nullable|string|max:150',
            'phone'        => 'nullable|string|max:30',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:500',
        ]);

        $booking = $this->resolveBooking($validated);

        // resolveBooking mengembalikan Response kalau identitas tidak valid.
        if ($booking instanceof Response) {
            return $booking;
        }

        // Hanya booking yang sudah selesai (atau lunas dan hasil foto sudah diisi) yang boleh dirating.
        $isFinished = $booking->booking_status === 'completed' || ($booking->isFullyPaid() && !empty($booking->drive_archive_url));
        if (! $isFinished) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan belum selesai, belum bisa diberi rating.',
            ], 422);
        }

        // 1 ulasan per booking.
        if (StudioReview::withoutGlobalScopes()->where('booking_id', $booking->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah pernah diberi rating.',
            ], 409);
        }

        $review = StudioReview::create([
            'tenant_id'     => $booking->tenant_id,
            'booking_id'    => $booking->id,
            'package_id'    => $booking->package_id,
            'customer_name' => $booking->customer_name,
            'rating'        => (int) $validated['rating'],
            'comment'       => $validated['comment'] ?? null,
        ]);

        try {
            $tenantUsers = \App\Models\User::where('tenant_id', $booking->tenant_id)->get();
            foreach ($tenantUsers as $admin) {
                $admin->notify(new \App\Notifications\NewReviewNotification($review));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed sending NewReviewNotification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data' => [
                'review' => [
                    'id'            => $review->id,
                    'customer_name' => $review->maskedName(),
                    'rating'        => $review->rating,
                    'comment'       => $review->comment,
                    'created_at'    => $review->created_at?->toIso8601String(),
                ],
            ],
        ], 201);
    }

    /**
     * Daftar ulasan + rata-rata rating sebuah studio.
     */
    public function index(?string $slug = null)
    {
        $subdomainTenant = request()->attributes->get('tenant');
        if ($subdomainTenant instanceof \App\Models\Tenant) {
            $tenant = $subdomainTenant;
        } elseif ($slug) {
            $tenant = Tenant::where('slug', $slug)->firstOrFail();
        } else {
            abort(404, 'Studio tidak ditemukan.');
        }

        // Hanya ulasan yang visible: ulasan yang disembunyikan super admin
        // tidak boleh bocor ke publik dan tidak boleh dihitung skornya.
        $reviews = StudioReview::withoutGlobalScopes()
            ->visible()
            ->where('tenant_id', $tenant->id)
            ->with('package:id,name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $aggregate = StudioReview::withoutGlobalScopes()
            ->visible()
            ->where('tenant_id', $tenant->id)
            ->selectRaw('COUNT(*) as reviews_count, AVG(rating) as avg_rating')
            ->first();

        $count = (int) ($aggregate->reviews_count ?? 0);

        return response()->json([
            'success' => true,
            'data' => [
                'rating'        => $count > 0 ? round((float) $aggregate->avg_rating, 1) : null,
                'reviews_count' => $count,
                'reviews'       => $reviews->map(fn ($review) => [
                    'id'            => $review->id,
                    'customer_name' => $review->maskedName(),
                    'rating'        => $review->rating,
                    'comment'       => $review->comment,
                    'created_at'    => $review->created_at?->toIso8601String(),
                    'package_name'  => $review->package?->name,
                ])->values(),
            ],
        ]);
    }

    /**
     * Tentukan booking yang boleh dirating dari identitas yang dikirim.
     *
     * @return Booking|Response
     */
    private function resolveBooking(array $data)
    {
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $code = trim((string) ($data['booking_code'] ?? ''));

        // ---- Opsi 1: pakai booking code -------------------------------
        if ($code !== '') {
            $booking = Booking::withoutGlobalScopes()->where('booking_code', $code)->first();

            if (! $booking) {
                return $this->fail('Kode booking tidak ditemukan.', 404);
            }

            // Bukti identitas: email atau HP harus cocok dengan booking.
            if ($email === '' && $phone === '') {
                return $this->fail('Masukkan email atau nomor WhatsApp yang dipakai saat booking untuk verifikasi.', 403);
            }

            $emailOk = $email !== '' && strcasecmp($email, (string) $booking->customer_email) === 0;
            $phoneOk = $phone !== '' && $this->samePhone($phone, (string) $booking->customer_phone);

            if (($email !== '' && ! $emailOk) || ($phone !== '' && ! $phoneOk)) {
                return $this->fail('Email/nomor WhatsApp tidak cocok dengan booking ini.', 403);
            }

            return $booking;
        }

        // ---- Opsi 2: pakai email atau nomor WhatsApp -------------------
        if ($email === '' && $phone === '') {
            return $this->fail('Isi kode booking, email, atau nomor WhatsApp.', 422);
        }

        $candidates = Booking::withoutGlobalScopes()
            ->where('booking_status', 'completed')
            ->where(function ($q) use ($email, $phone) {
                if ($email !== '') {
                    $q->whereRaw('LOWER(customer_email) = ?', [strtolower($email)]);
                }

                if ($phone !== '') {
                    // Cocokkan 9 digit terakhir (menutup beda format 0xxx / 62xxx).
                    $q->orWhere('customer_phone', 'like', '%'.substr($this->normalizePhone($phone), -9).'%');
                }
            })
            ->with('package:id,name')
            ->orderByDesc('booking_date')
            ->get();

        if ($candidates->isEmpty()) {
            return $this->fail('Belum ada pesanan selesai dengan email/nomor tersebut.', 404);
        }

        if ($candidates->count() > 1) {
            return response()->json([
                'success'    => false,
                'message'    => 'Ada beberapa pesanan selesai. Pilih kode booking yang ingin dirating.',
                'candidates' => $candidates->map(fn ($b) => [
                    'booking_code' => $b->booking_code,
                    'booking_date' => $b->booking_date?->toDateString(),
                    'package_name' => $b->package?->name,
                ])->values(),
            ], 422);
        }

        return $candidates->first();
    }

    /**
     * Response error standar.
     */
    private function fail(string $message, int $status): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Samakan format nomor HP (62xxx vs 0xxx).
     */
    private function samePhone(string $a, string $b): bool
    {
        $a = $this->normalizePhone($a);
        $b = $this->normalizePhone($b);

        if ($a === '' || $b === '') {
            return false;
        }

        if ($a === $b) {
            return true;
        }

        // Bandingkan 9 digit terakhir (abaikan beda 0/62).
        return substr($a, -9) === substr($b, -9);
    }

    private function normalizePhone(string $value): string
    {
        return (string) preg_replace('/\D/', '', $value);
    }
}
