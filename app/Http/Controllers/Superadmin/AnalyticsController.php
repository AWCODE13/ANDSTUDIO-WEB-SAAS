<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Tenant;
use App\Services\AnalyticsExportService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $period = request('period', 'monthly');
        $tenantId = request('tenant_id');

        // Normalisasi rentang tanggal default berdasarkan periode jika tidak diisi eksplisit
        $defaultStartDate = match ($period) {
            'daily' => now()->subDays(29)->startOfDay(),
            'weekly' => now()->subWeeks(11)->startOfWeek()->startOfDay(),
            default => now()->subMonths(5)->startOfMonth()->startOfDay(),
        };
        $defaultEndDate = now()->endOfDay();

        $startDate = request('start_date') ? Carbon::parse(request('start_date'))->startOfDay() : $defaultStartDate;
        $endDate = request('end_date') ? Carbon::parse(request('end_date'))->endOfDay() : $defaultEndDate;

        // Get all tenants untuk filter
        $tenants = Tenant::where('subscription_status', 'active')->orderBy('name')->get();

        // 1. Data Periode Saat Ini
        $bookingsQuery = Booking::whereBetween('created_at', [$startDate, $endDate]);
        $expensesQuery = Expense::whereBetween('created_at', [$startDate, $endDate]);

        if ($tenantId) {
            $bookingsQuery->where('tenant_id', $tenantId);
            $expensesQuery->where('tenant_id', $tenantId);
        }

        $bookings = $bookingsQuery->get();
        $expenses = $expensesQuery->get();

        $totalRevenue = (float) $bookings->sum('total_amount');
        $totalExpense = (float) $expenses->sum('amount');
        $totalProfit = $totalRevenue - $totalExpense;
        $totalBookings = (int) $bookings->count();

        // 2. Data Periode Sebelumnya (durasi hari yang sama persis)
        $durationInDays = (int) $startDate->diffInDays($endDate) + 1;
        $prevEndDate = $startDate->copy()->subDay()->endOfDay();
        $prevStartDate = $prevEndDate->copy()->subDays($durationInDays - 1)->startOfDay();

        $prevBookingsQuery = Booking::whereBetween('created_at', [$prevStartDate, $prevEndDate]);
        $prevExpensesQuery = Expense::whereBetween('created_at', [$prevStartDate, $prevEndDate]);

        if ($tenantId) {
            $prevBookingsQuery->where('tenant_id', $tenantId);
            $prevExpensesQuery->where('tenant_id', $tenantId);
        }

        $prevBookings = $prevBookingsQuery->get();
        $prevExpenses = $prevExpensesQuery->get();

        $prevRevenue = (float) $prevBookings->sum('total_amount');
        $prevExpense = (float) $prevExpenses->sum('amount');
        $prevProfit = $prevRevenue - $prevExpense;
        $prevBookingsCount = (int) $prevBookings->count();

        // 3. Persentase Perubahan vs Periode Lalu
        $revenueChange = $this->calculatePercentageChange($totalRevenue, $prevRevenue);
        $expenseChange = $this->calculatePercentageChange($totalExpense, $prevExpense);
        $profitChange  = $this->calculatePercentageChange($totalProfit, $prevProfit);
        $bookingChange = $this->calculatePercentageChange($totalBookings, $prevBookingsCount);

        // Chart data
        $chartData = $this->groupChartData($bookings, $expenses, $period, $startDate, $endDate);

        // Top tenants by revenue
        $topTenants = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->with('tenant')
            ->get()
            ->groupBy('tenant_id')
            ->map(function ($group) {
                $tenant = $group->first()->tenant;
                return [
                    'tenant_id' => $tenant->id,
                    'name' => $tenant->name ?? 'N/A',
                    'revenue' => (float) $group->sum('total_amount'),
                    'bookings' => (int) $group->count(),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        // Expense categories
        $expenseCategories = $expenses->groupBy('category')
            ->map(fn($group) => (object)['category' => $group[0]->category, 'total' => (float) $group->sum('amount')])
            ->sortByDesc('total')
            ->values();

        return view('superadmin.analytics.index', compact(
            'period', 'tenantId', 'startDate', 'endDate', 'tenants',
            'totalRevenue', 'totalExpense', 'totalProfit', 'totalBookings',
            'revenueChange', 'expenseChange', 'profitChange', 'bookingChange',
            'chartData', 'topTenants', 'expenseCategories'
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
        $tenantId = request('tenant_id');
        $startDate = Carbon::parse(request('start_date', now()->subMonths(1)->startOfMonth()));
        $endDate = Carbon::parse(request('end_date', now()->endOfMonth()));

        $bookingsQuery = Booking::whereBetween('created_at', [$startDate, $endDate])->with('tenant');
        $expensesQuery = Expense::whereBetween('created_at', [$startDate, $endDate])->with('tenant');

        $subjectName = 'Semua Studio (Platform Aggregate)';
        if ($tenantId) {
            $bookingsQuery->where('tenant_id', $tenantId);
            $expensesQuery->where('tenant_id', $tenantId);
            $t = Tenant::find($tenantId);
            if ($t) {
                $subjectName = 'Studio: ' . $t->name;
            }
        }

        $bookings = $bookingsQuery->orderBy('created_at')->get();
        $expenses = $expensesQuery->orderBy('created_at')->get();

        $filename = 'laporan_analytics_platform_' . now()->format('Ymd_His') . '.xlsx';

        return $exporter->export(
            scopeTitle: 'Laporan Keuangan & Analytics Platform',
            subjectName: $subjectName,
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
