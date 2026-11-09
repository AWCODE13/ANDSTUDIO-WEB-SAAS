<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expense;
use App\Services\AnalyticsExportService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $tenant = Auth::user()->tenant;
        $period = request('period', 'monthly');

        // Normalisasi rentang tanggal default berdasarkan periode jika tidak diisi eksplisit
        $defaultStartDate = match ($period) {
            'daily' => now()->subDays(29)->startOfDay(),
            'weekly' => now()->subWeeks(11)->startOfWeek()->startOfDay(),
            default => now()->subMonths(5)->startOfMonth()->startOfDay(),
        };
        $defaultEndDate = now()->endOfDay();

        $startDate = request('start_date') ? Carbon::parse(request('start_date'))->startOfDay() : $defaultStartDate;
        $endDate = request('end_date') ? Carbon::parse(request('end_date'))->endOfDay() : $defaultEndDate;

        // 1. Data Periode Saat Ini
        $bookings = Booking::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $expenses = Expense::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalRevenue = (float) $bookings->sum('total_amount');
        $totalExpense = (float) $expenses->sum('amount');
        $totalProfit = $totalRevenue - $totalExpense;
        $totalBookings = (int) $bookings->count();

        // 2. Data Periode Sebelumnya (durasi hari yang sama persis)
        $durationInDays = (int) $startDate->diffInDays($endDate) + 1;
        $prevEndDate = $startDate->copy()->subDay()->endOfDay();
        $prevStartDate = $prevEndDate->copy()->subDays($durationInDays - 1)->startOfDay();

        $prevBookings = Booking::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->get();

        $prevExpenses = Expense::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->get();

        $prevRevenue = (float) $prevBookings->sum('total_amount');
        $prevExpense = (float) $prevExpenses->sum('amount');
        $prevProfit = $prevRevenue - $prevExpense;
        $prevBookingsCount = (int) $prevBookings->count();

        // 3. Persentase Perubahan vs Periode Lalu
        $revenueChange = $this->calculatePercentageChange($totalRevenue, $prevRevenue);
        $expenseChange = $this->calculatePercentageChange($totalExpense, $prevExpense);
        $profitChange  = $this->calculatePercentageChange($totalProfit, $prevProfit);
        $bookingChange = $this->calculatePercentageChange($totalBookings, $prevBookingsCount);

        // Group data untuk chart berdasarkan period
        $chartData = $this->groupChartData($bookings, $expenses, $period, $startDate, $endDate);

        // Expense categories
        $expenseCategories = $expenses->groupBy('category')
            ->map(fn($group) => (object)['category' => $group[0]->category, 'total' => (float) $group->sum('amount')])
            ->sortByDesc('total')
            ->values();

        return view('tenant.analytics.index', compact(
            'period', 'startDate', 'endDate',
            'totalRevenue', 'totalExpense', 'totalProfit', 'totalBookings',
            'revenueChange', 'expenseChange', 'profitChange', 'bookingChange',
            'chartData', 'expenseCategories'
        ));
    }

    private function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            if ($current > 0) {
                return 100.0;
            } elseif ($current < 0) {
                return -100.0;
            }
            return 0.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    public function export(AnalyticsExportService $exporter)
    {
        $tenant = Auth::user()->tenant;
        $startDate = Carbon::parse(request('start_date', now()->subMonths(1)->startOfMonth()));
        $endDate = Carbon::parse(request('end_date', now()->endOfMonth()));

        $bookings = Booking::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $expenses = Expense::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $filename = 'laporan_keuangan_' . $tenant->slug . '_' . now()->format('Ymd_His') . '.xlsx';

        return $exporter->export(
            scopeTitle: 'Laporan Keuangan Studio',
            subjectName: $tenant->name,
            startDate: $startDate,
            endDate: $endDate,
            bookings: $bookings,
            expenses: $expenses,
            filename: $filename
        );
    }

    private function groupChartData($bookings, $expenses, $period, $startDate, $endDate)
    {
        $data = [];

        if ($period === 'daily') {
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $date = $current->format('Y-m-d');
                $dayBookings = $bookings->filter(fn($b) => $b->created_at->format('Y-m-d') === $date)->sum('total_amount');
                $dayExpenses = $expenses->filter(fn($e) => $e->created_at->format('Y-m-d') === $date)->sum('amount');

                $data[] = [
                    'label' => $current->format('d M'),
                    'revenue' => $dayBookings,
                    'expense' => $dayExpenses,
                    'profit' => $dayBookings - $dayExpenses,
                ];

                $current->addDay();
            }
        } elseif ($period === 'weekly') {
            $current = $startDate->copy()->startOfWeek();
            while ($current <= $endDate) {
                $weekEnd = $current->copy()->endOfWeek();
                $weekBookings = $bookings->filter(fn($b) => $b->created_at >= $current && $b->created_at <= $weekEnd)->sum('total_amount');
                $weekExpenses = $expenses->filter(fn($e) => $e->created_at >= $current && $e->created_at <= $weekEnd)->sum('amount');

                $data[] = [
                    'label' => 'W' . $current->weekOfYear,
                    'revenue' => $weekBookings,
                    'expense' => $weekExpenses,
                    'profit' => $weekBookings - $weekExpenses,
                ];

                $current->addWeek();
            }
        } else {
            $current = $startDate->copy()->startOfMonth();
            while ($current <= $endDate) {
                $monthEnd = $current->copy()->endOfMonth();
                $monthBookings = $bookings->filter(fn($b) => $b->created_at >= $current && $b->created_at <= $monthEnd)->sum('total_amount');
                $monthExpenses = $expenses->filter(fn($e) => $e->created_at >= $current && $e->created_at <= $monthEnd)->sum('amount');

                $data[] = [
                    'label' => $current->format('M Y'),
                    'revenue' => $monthBookings,
                    'expense' => $monthExpenses,
                    'profit' => $monthBookings - $monthExpenses,
                ];

                $current->addMonth();
            }
        }

        return $data;
    }
}
