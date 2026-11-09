<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Expense;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $today = now()->toDateString();

        // --- 1. Statistik Utama + Trend MoM ---
        // Sesi Bulan Ini
        $totalBookings = Booking::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $lastMonthBookings = Booking::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $bookingTrend = $lastMonthBookings > 0
            ? round((($totalBookings - $lastMonthBookings) / $lastMonthBookings) * 100)
            : ($totalBookings > 0 ? 100 : 0);

        // Perlu Validasi DP
        $pendingPayments = Payment::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        // Total Pendapatan (Verified) Bulan Ini + Trend
        $totalRevenue = Payment::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'verified')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        $lastMonthRevenue = Payment::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'verified')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');
        $revenueTrend = $lastMonthRevenue > 0
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : ($totalRevenue > 0 ? 100 : 0);

        // Total Pengeluaran & Laba Bersih (all-time)
        $totalExpense = Expense::where('tenant_id', $tenantId)->sum('amount');
        $netProfit = $totalRevenue - $totalExpense;

        // --- 2. Data Tren 7 Hari Terakhir ---
        $dailyTrends = [];
        $total7DaysRevenue = 0;
        $total7DaysBookings = 0;
        for ($i = 6; $i >= 0; $i--) {
            $carbonDate = now()->subDays($i);
            $date = $carbonDate->toDateString();

            $dayBookings = Booking::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->whereDate('booking_date', $date)
                ->count();

            $dayRevenue = (float) Payment::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('status', 'verified')
                ->whereDate('created_at', $date)
                ->sum('amount');

            $total7DaysRevenue += $dayRevenue;
            $total7DaysBookings += $dayBookings;

            $dailyTrends[] = [
                'date'        => $date,
                'label'       => $carbonDate->format('d M'),
                'day_name'    => $carbonDate->translatedFormat('D'),
                'is_today'    => $carbonDate->isToday(),
                'bookings'    => $dayBookings,
                'revenue'     => $dayRevenue,
            ];
        }

        $maxRevenue = max(array_column($dailyTrends, 'revenue')) ?: 1;
        $maxBookings = max(array_column($dailyTrends, 'bookings')) ?: 1;
        foreach ($dailyTrends as &$item) {
            $item['revenue_percent'] = $maxRevenue > 0 ? round(($item['revenue'] / $maxRevenue) * 100) : 0;
            $item['bookings_percent'] = $maxBookings > 0 ? round(($item['bookings'] / $maxBookings) * 100) : 0;
        }
        unset($item);

        // --- 3. Sesi Hari Ini (Timeline) ---
        $todayBookings = Booking::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereDate('booking_date', $today)
            ->with(['package', 'payments'])
            ->orderBy('start_time')
            ->get();

        // Okupansi Hari Ini
        $todaySlotsTotal = BookingSlot::where('tenant_id', $tenantId)
            ->whereDate('date', $today)
            ->whereIn('status', ['available', 'reserved'])
            ->count();
        $todaySlotsBooked = BookingSlot::where('tenant_id', $tenantId)
            ->whereDate('date', $today)
            ->where('status', 'reserved')
            ->count();
        $occupancyPercent = $todaySlotsTotal > 0
            ? round(($todaySlotsBooked / $todaySlotsTotal) * 100)
            : 0;

        // --- 4. Reservasi Terbaru ---
        $recentBookings = Booking::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['package', 'payments'])
            ->latest()
            ->take(5)
            ->get();

        return view('tenant.dashboard', compact(
            'totalBookings',
            'bookingTrend',
            'pendingPayments',
            'totalRevenue',
            'revenueTrend',
            'totalExpense',
            'netProfit',
            'dailyTrends',
            'total7DaysRevenue',
            'total7DaysBookings',
            'todayBookings',
            'todaySlotsTotal',
            'todaySlotsBooked',
            'occupancyPercent',
            'recentBookings'
        ));
    }

    // Verifikasi atau Tolak Pembayaran DP / Pelunasan
    public function verifyPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'action' => 'required|in:verified,rejected',
            'admin_rejection_note' => 'nullable|string|max:255',
        ]);

        $payment->update([
            'status' => $request->action,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'admin_rejection_note' => $request->admin_rejection_note,
        ]);

        // Perbarui status booking terkait jika DP terverifikasi
        $booking = $payment->booking;
        if ($request->action === 'verified') {
            $totalPaid = $booking->payments()->where('status', 'verified')->sum('amount');
            $booking->update([
                'paid_amount' => $totalPaid,
                'booking_status' => 'confirmed',
                'payment_status' => $totalPaid >= $booking->total_amount ? 'paid' : 'partially_paid',
            ]);
        }

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    // Update Tautan Google Drive Hasil Foto & Status Reservasi
    // (tanpa kirim WA; kirim WA lewat halaman Daftar Reservasi)
    public function updateBookingArchive(Request $request, Booking $booking)
    {
        $request->validate([
            'archive_link'   => 'nullable|url',
            'booking_status' => 'required|in:pending,confirmed,in_progress,completed,canceled',
        ]);

        $booking->update([
            'archive_link'   => $request->archive_link,
            'booking_status' => $request->booking_status,
        ]);

        return back()->with('success', 'Data booking & tautan Google Drive berhasil disimpan.');
    }
}