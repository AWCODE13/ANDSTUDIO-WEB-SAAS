<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Tampilkan daftar pelanggan & riwayat transaksinya dengan strict tenant isolation.
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $search = $request->get('q');

        // Ambil semua booking milik tenant ini dengan strict tenant isolation
        $query = Booking::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['payments', 'package'])
            ->latest('booking_date');

        if ($search) {
            $query->where(function ($w) use ($search) {
                $w->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $bookings = $query->get();

        // Grouping berdasarkan nomor telepon atau email pelanggan
        $customers = $bookings->groupBy(function ($booking) {
            $phone = trim((string) $booking->customer_phone);
            $email = trim(strtolower((string) $booking->customer_email));
            return $phone !== '' ? $phone : ($email !== '' ? $email : 'unknown_' . $booking->id);
        })->map(function ($customerBookings) {
            $latest = $customerBookings->first();
            $totalBookings = $customerBookings->count();
            
            // Total spent: total pembayaran terverifikasi
            $totalSpent = $customerBookings->sum(function ($b) {
                return $b->payments->where('status', 'verified')->sum('amount');
            });

            $lastBookingDate = $customerBookings->max('booking_date');

            return [
                'name'              => $latest->customer_name,
                'email'             => $latest->customer_email,
                'phone'             => $latest->customer_phone,
                'total_bookings'    => $totalBookings,
                'total_spent'       => $totalSpent,
                'last_booking_date' => $lastBookingDate ? \Carbon\Carbon::parse($lastBookingDate) : null,
                'bookings'          => $customerBookings,
                'booking_history'   => $customerBookings,
            ];
        })->sortByDesc('last_booking_date')->values();

        // Statistik ringkas pelanggan
        $stats = [
            'total_customers' => $customers->count(),
            'total_revenue'   => $customers->sum('total_spent'),
            'repeat_customers'=> $customers->where('total_bookings', '>', 1)->count(),
        ];

        return view('tenant.customers.index', compact('customers', 'stats', 'search'));
    }
}
