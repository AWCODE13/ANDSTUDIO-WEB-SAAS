@extends('layouts.tenant')

@section('title', __('tenant_dashboard.title'))
@section('page_title', __('tenant_dashboard.title'))

@section('content')
    {{-- =========================================
         HEADER — Welcome + Status Badge
       ========================================= --}}
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content">
            {{ __('tenant_dashboard.welcome', ['name' => Auth::user()->name]) }}
        </h1>
        <p class="text-base-content/60 text-sm mt-1">
            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </p>
        {{-- Occupancy Badge --}}
        @if($todaySlotsTotal > 0)
        <span class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            {{ $todaySlotsBooked }} / {{ $todaySlotsTotal }} slot terisi hari ini ({{ $occupancyPercent }}%)
        </span>
        @endif
    </div>

    {{-- =========================================
         TOP GRID — 4 Summary Cards (Solid, Consistent Style)
       ========================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" role="region" aria-label="Statistik Utama">
        {{-- Card 1: Sesi Bulan Ini --}}
        <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base-content/60 text-sm font-medium mb-1">{{ __('tenant_dashboard.stat_sessions') }}</p>
                    <h2 class="text-3xl font-extrabold text-base-content">{{ number_format($totalBookings) }}</h2>
                    @if($bookingTrend !== 0)
                    <span class="inline-flex items-center gap-1 mt-2 px-2.5 py-1 rounded-full text-xs font-bold
                        {{ $bookingTrend >= 0 ? 'bg-success/10 text-success border border-success/20' : 'bg-error/10 text-error border border-error/20' }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $bookingTrend >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/></svg>
                        {{ abs($bookingTrend) }}% {{ __('tenant_dashboard.vs_last_month') }}
                    </span>
                    @endif
                </div>
                <div class="w-14 h-14 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </article>

        {{-- Card 2: Perlu Validasi DP (Emerald/Teal theme) --}}
        <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm hover:shadow-md transition-shadow duration-300
            {{ $pendingPayments > 0 ? 'ring-2 ring-emerald-500/30 animate-pulse-subtle' : '' }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base-content/60 text-sm font-medium mb-1">{{ __('tenant_dashboard.stat_pending_dp') }}</p>
                    <h2 class="text-3xl font-extrabold {{ $pendingPayments > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-base-content' }}">{{ number_format($pendingPayments) }}</h2>
                    @if($pendingPayments > 0)
                    <span class="inline-flex items-center gap-1 mt-2 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('tenant_dashboard.needs_action') }}
                    </span>
                    @endif
                </div>
                <div class="w-14 h-14 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </article>

        {{-- Card 3: Total Kas Masuk (Verified) --}}
        <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base-content/60 text-sm font-medium mb-1">{{ __('tenant_dashboard.stat_revenue') }}</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h2>
                    @if($revenueTrend !== 0)
                    <span class="inline-flex items-center gap-1 mt-2 px-2.5 py-1 rounded-full text-xs font-bold
                        {{ $revenueTrend >= 0 ? 'bg-success/10 text-success border border-success/20' : 'bg-error/10 text-error border border-error/20' }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $revenueTrend >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/></svg>
                        {{ abs($revenueTrend) }}% {{ __('tenant_dashboard.vs_last_month') }}
                    </span>
                    @endif
                </div>
                <div class="w-14 h-14 rounded-xl bg-success/10 dark:bg-success/20 flex items-center justify-center text-success">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </article>

        {{-- Card 4: Saldo / Laba Bersih --}}
        <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base-content/60 text-sm font-medium mb-1">{{ __('tenant_dashboard.stat_net_profit') }}</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold {{ $netProfit >= 0 ? 'text-success' : 'text-error' }}">
                        Rp {{ number_format(abs($netProfit), 0, ',', '.') }}
                        @if($netProfit < 0) <span class="text-error">(-)</span> @endif
                    </h2>
                    <p class="text-base-content/50 text-xs mt-1">
                        {{ __('tenant_dashboard.expenses') }}: Rp {{ number_format($totalExpense, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-14 h-14 rounded-xl {{ $netProfit >= 0 ? 'bg-success/10 dark:bg-success/20 text-success' : 'bg-error/10 dark:bg-error/20 text-error' }} flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
        </article>
    </div>

    {{-- =========================================
         MID SECTION — Native Trend Summary (No Chart.js) + Occupancy
       ========================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Trend Summary (2/3) --}}
        <article class="lg:col-span-2 rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm">
            @php $totalRevenue7d = $total7DaysRevenue ?? 0; $totalBookings7d = $total7DaysBookings ?? 0; @endphp
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <h3 class="text-lg font-bold text-base-content">{{ __('tenant_dashboard.chart.title') }}</h3>
                <div class="flex items-center gap-2 sm:ml-auto">
                    <span class="px-3 py-1 rounded-lg text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                        {{ __('tenant_dashboard.chart.revenue') }}: <strong>Rp {{ number_format($totalRevenue7d, 0, ',', '.') }}</strong>
                    </span>
                    <span class="px-3 py-1 rounded-lg text-xs font-medium bg-primary/10 text-primary border border-primary/20 whitespace-nowrap">
                        {{ __('tenant_dashboard.chart.sessions') }}: <strong>{{ $totalBookings7d }}</strong>
                    </span>
                </div>
            </div>

            {{-- Mini Stat Row --}}
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="rounded-xl bg-base-200 dark:bg-base-800/50 p-3 text-center">
                    <div class="text-2xl font-extrabold text-base-content">{{ number_format($totalBookings7d) }}</div>
                    <div class="text-xs text-base-content/60">{{ __('tenant_dashboard.total_sessions') }}</div>
                </div>
                <div class="rounded-xl bg-base-200 dark:bg-base-800/50 p-3 text-center">
                    <div class="text-2xl font-extrabold text-success">Rp {{ number_format($totalRevenue7d, 0, ',', '.') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('tenant_dashboard.total_revenue_short') }}</div>
                </div>
                <div class="rounded-xl bg-base-200 dark:bg-base-800/50 p-3 text-center">
                    <div class="text-2xl font-extrabold text-primary">
                        {{ $totalBookings7d > 0 ? 'Rp ' . number_format($totalRevenue7d / $totalBookings7d, 0, ',', '.') : 'Rp 0' }}
                    </div>
                    <div class="text-xs text-base-content/60">{{ __('tenant_dashboard.avg_per_session') }}</div>
                </div>
            </div>

            {{-- Daily Trend List with CSS Bars --}}
            <div class="space-y-2 max-h-64 overflow-y-auto pr-1" role="list" aria-label="Tren harian 14 hari terakhir">
                @foreach($dailyTrends as $item)
                    <div class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-base-200/50 dark:hover:bg-base-800/50 transition-colors
                        {{ $item['is_today'] ? 'bg-emerald-500/5 dark:bg-emerald-500/10 ring-1 ring-emerald-500/20' : '' }}"
                        role="listitem">
                        <div class="w-16 shrink-0 text-right">
                            <div class="font-semibold text-sm text-base-content">{{ $item['label'] }}</div>
                            <div class="text-xs text-base-content/50">{{ $item['day_name'] }}</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-20 text-right font-medium text-emerald-600 dark:text-emerald-400"
                                    title="{{ $item['bookings'] }} {{ __('tenant_dashboard.sessions_short') }}">
                                    {{ $item['bookings'] }}
                                </span>
                                <div class="flex-1 h-3 bg-base-200 dark:bg-base-700 rounded-full overflow-hidden" role="progressbar" aria-valuenow="{{ $item['bookings_percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $item['bookings_percent'] }}%"></div>
                                </div>
                                <span class="w-24 text-right font-medium text-primary"
                                    title="Rp {{ number_format($item['revenue'], 0, ',', '.') }}">
                                    {{ $item['revenue'] > 0 ? 'Rp ' . number_format($item['revenue'], 0, ',', '.') : '-' }}
                                </span>
                                <div class="flex-1 h-3 bg-base-200 dark:bg-base-700 rounded-full overflow-hidden" role="progressbar" aria-valuenow="{{ $item['revenue_percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: {{ $item['revenue_percent'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        @if($item['is_today'])
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex-shrink-0">
                            {{ __('tenant_dashboard.today_badge') }}
                        </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>

        {{-- Occupancy Mini Card (1/3) --}}
        <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm">
            <h3 class="text-lg font-bold text-base-content mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                {{ __('tenant_dashboard.occupancy_today') }}
            </h3>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-base-content/70">{{ __('tenant_dashboard.occupancy_filled') }}</span>
                        <span class="font-bold text-base-content">{{ $todaySlotsBooked }} / {{ $todaySlotsTotal }}</span>
                    </div>
                    <div class="h-2 bg-base-200 rounded-full overflow-hidden" role="progressbar" aria-valuenow="{{ $occupancyPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Persentase okupansi studio hari ini">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $occupancyPercent }}%"></div>
                    </div>
                    <p class="text-xs text-base-content/50 mt-1 text-right">{{ $occupancyPercent }}% {{ __('tenant_dashboard.occupancy_rate') }}</p>
                </div>
                @if($todaySlotsTotal - $todaySlotsBooked > 0)
                <a href="{{ route('tenant.booking-slots.index') }}" class="block text-center px-3 py-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold hover:bg-emerald-500/20 transition">
                    {{ __('tenant_dashboard.occupancy_manage_slots') }}
                </a>
                @endif
            </div>
        </article>
    </div>

    {{-- =========================================
         BOTTOM GRID — Recent Bookings (2/3) + Today's Agenda + Quick Actions (1/3)
       ========================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT: Recent Bookings Table --}}
        <article class="lg:col-span-2 rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-base-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-base-content">{{ __('tenant_dashboard.table.title') }}</h3>
                <a href="{{ route('tenant.bookings.index') }}" class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                    {{ __('tenant_dashboard.table.view_all') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left text-sm" role="table">
                    <thead class="bg-base-200/50 text-base-content/60 font-semibold border-b border-base-200">
                        <tr>
                            <th class="px-5 py-3">{{ __('tenant_dashboard.table.client') }}</th>
                            <th class="px-5 py-3">{{ __('tenant_dashboard.table.package') }}</th>
                            <th class="px-5 py-3">{{ __('tenant_dashboard.table.payment') }}</th>
                            <th class="px-5 py-3">{{ __('tenant_dashboard.table.status') }}</th>
                            <th class="px-5 py-3">{{ __('tenant_dashboard.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200/50">
                        @forelse ($recentBookings as $index => $booking)
                            @php
                                $latestPayment = $booking->payments->first();
                                $rowClass = $index % 2 === 0 ? 'bg-base-200/30' : 'bg-transparent';
                            @endphp
                            <tr class="{{ $rowClass }} hover:bg-base-200/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 flex items-center justify-center font-bold text-sm uppercase">
                                            {{ substr($booking->customer_name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-base-content">{{ $booking->customer_name }}</div>
                                            <div class="text-emerald-600 dark:text-emerald-400 font-mono text-xs mt-0.5">{{ $booking->booking_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-base-content">{{ $booking->package->name }}</div>
                                    <div class="text-base-content/60 text-xs mt-0.5">
                                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }} | {{ substr($booking->start_time, 0, 5) }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-success">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</div>
                                    @if($latestPayment)
                                        <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            {{ $latestPayment->status === 'verified' ? 'bg-success/10 text-success border border-success/20' : ($latestPayment->status === 'pending' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-error/10 text-error border border-error/20') }}">
                                            {{ ucfirst($latestPayment->status) }}
                                            @if($latestPayment->status === 'pending')
                                                <svg class="w-2.5 h-2.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-base-content/50 italic text-xs">{{ __('tenant_dashboard.no_payment') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                        {{ $booking->booking_status === 'confirmed' ? 'bg-success/10 text-success border border-success/20' :
                                            ($booking->booking_status === 'canceled' ? 'bg-error/10 text-error border border-error/20' :
                                            ($booking->booking_status === 'completed' ? 'bg-primary/10 text-primary border border-primary/20' :
                                            'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20')) }}">
                                        {{ ucfirst($booking->booking_status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($latestPayment && $latestPayment->status === 'pending')
                                            <form action="{{ route('tenant.payments.verify', $latestPayment->id) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="action" value="verified">
                                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-success text-white hover:bg-success/90 transition flex items-center gap-1" title="Terima">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('tenant.payments.verify', $latestPayment->id) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="action" value="rejected">
                                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-error text-white hover:bg-error/90 transition flex items-center gap-1" title="Tolak">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('tenant.bookings.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-base-200 text-base-content hover:bg-base-300 transition flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-12 h-12 text-base-content/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                        <p class="font-medium text-base-content/60">{{ __('tenant_dashboard.empty') }}</p>
                                        <a href="{{ route('tenant.bookings.create') }}" class="mt-2 px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-500 text-white hover:bg-emerald-600 transition">{{ __('tenant_dashboard.create_first') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="lg:hidden divide-y divide-base-200/50">
                @forelse ($recentBookings as $booking)
                    @php $latestPayment = $booking->payments->first(); @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 flex items-center justify-center font-bold text-sm uppercase">
                                {{ substr($booking->customer_name, 0, 2) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-base-content truncate">{{ $booking->customer_name }}</div>
                                <div class="text-emerald-600 dark:text-emerald-400 font-mono text-xs mt-0.5">{{ $booking->booking_code }}</div>
                            </div>
                        </div>
                        <div class="text-sm text-base-content/70">
                            {{ $booking->package->name }} • {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }} {{ substr($booking->start_time, 0, 5) }}
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-success">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</div>
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                {{ $booking->booking_status === 'confirmed' ? 'bg-success/10 text-success border border-success/20' :
                                    ($booking->booking_status === 'canceled' ? 'bg-error/10 text-error border border-error/20' :
                                    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20') }}">
                                {{ ucfirst($booking->booking_status) }}
                            </span>
                        </div>
                        @if($latestPayment && $latestPayment->status === 'pending')
                            <div class="flex gap-2">
                                <form action="{{ route('tenant.payments.verify', $latestPayment->id) }}" method="POST" class="flex-1">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="verified">
                                    <button class="w-full bg-success text-white py-2 rounded-lg text-xs font-bold hover:bg-success/90 transition">{{ __('tenant_dashboard.btn_accept') }}</button>
                                </form>
                                <form action="{{ route('tenant.payments.verify', $latestPayment->id) }}" method="POST" class="flex-1">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="rejected">
                                    <button class="w-full bg-error text-white py-2 rounded-lg text-xs font-bold hover:bg-error/90 transition">{{ __('tenant_dashboard.btn_reject') }}</button>
                                </form>
                            </div>
                        @endif
                        <a href="{{ route('tenant.bookings.index') }}" class="block text-center px-3 py-2 rounded-lg bg-base-200 text-base-content hover:bg-base-300 text-sm font-semibold transition">
                            {{ __('tenant_dashboard.btn_detail') }}
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="font-medium text-base-content/60">{{ __('tenant_dashboard.empty') }}</p>
                    </div>
                @endforelse
            </div>
        </article>

        {{-- RIGHT: Today's Agenda + Quick Actions --}}
        <div class="space-y-6">
            {{-- Today's Agenda Card --}}
            <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm">
                <h3 class="text-lg font-bold text-base-content mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __('tenant_dashboard.today_sessions') }}
                </h3>
                @if($todayBookings->isNotEmpty())
                    <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                        @foreach($todayBookings as $booking)
                            @php $paymentStatus = $booking->payments->first()?->status ?? 'none'; @endphp
                            <div class="relative p-3 rounded-xl bg-base-200/50 dark:bg-base-800/50 border border-base-200/50 hover:bg-base-200/70 dark:hover:bg-base-700/50 transition-colors group">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 flex items-center justify-center font-bold text-sm uppercase flex-shrink-0">
                                        {{ substr($booking->customer_name, 0, 2) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="font-semibold text-base-content truncate">{{ $booking->customer_name }}</div>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 flex-shrink-0">
                                                {{ substr($booking->start_time, 0, 5) }}–{{ substr($booking->end_time, 0, 5) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-base-content/70 mt-0.5 flex items-center gap-2 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-primary/10 text-primary border border-primary/20">{{ $booking->package->name }}</span>
                                            @if($paymentStatus === 'pending')
                                                <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                                                    <svg class="w-2.5 h-2.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ __('tenant_dashboard.payment_pending') }}
                                                </span>
                                            @elseif($paymentStatus === 'verified')
                                                <span class="px-2 py-0.5 rounded-full text-xs bg-success/10 text-success border border-success/20">{{ __('tenant_dashboard.payment_verified') }}</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs bg-base-200 text-base-content/70">{{ __('tenant_dashboard.no_payment') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- WhatsApp Quick Action --}}
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->customer_phone) }}?text={{ urlencode('Halo ' . $booking->customer_name . ', ini pengingat sesi pemotretan Anda hari ini pukul ' . substr($booking->start_time, 0, 5) . ' untuk paket ' . $booking->package->name . '. Mohon datang tepat waktu. Terima kasih!') }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="absolute top-3 right-3 p-2 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 transition opacity-0 group-hover:opacity-100"
                                    aria-label="Kirim WA ke {{ $booking->customer_name }}"
                                    title="Kirim WA Pengingat">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.454.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.148-.669-1.611-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.194 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378 9.86 9.86 0 01-.437-.063l-3.927-.724c-.218-.04-.32-.293-.233-.484l.978-2.152c.088-.194.328-.333.577-.333h.663c.329 0 .62.198.76.459l2.08 3.983c.17.325.09.683-.17.883-.263.2-.578.298-.924.298h-.662zm10.418-13.785A12.066 12.066 0 0012 2C6.477 2 2 6.477 2 12c0 2.535.946 4.896 2.553 6.689l-1.595 4.378c-.125.343.187.672.495.587l3.162-.847c2.184-.585 4.147-1.863 5.605-3.704a12.07 12.07 0 002.194-7.627 12.055 12.055 0 00-2.133-4.794z"/></svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-base-content/20 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-base-content/60 text-sm">{{ __('tenant_dashboard.no_sessions_today') }}</p>
                        <a href="{{ route('tenant.booking-slots.index') }}" class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition">
                            {{ __('tenant_dashboard.add_slots') }}
                        </a>
                    </div>
                @endif
            </article>

            {{-- Quick Actions Card --}}
            <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm">
                <h3 class="text-lg font-bold text-base-content mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    {{ __('tenant_dashboard.quick_actions') }}
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('tenant.bookings.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-200/50 dark:bg-base-800/50 border border-base-200/50 hover:bg-base-200/70 dark:hover:bg-base-700/50 transition-colors group">
                        <div class="w-11 h-11 rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-base-content text-center">{{ __('tenant_dashboard.action_manual_booking') }}</span>
                    </a>
                    <a href="{{ route('tenant.withdrawals.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-200/50 dark:bg-base-800/50 border border-base-200/50 hover:bg-base-200/70 dark:hover:bg-base-700/50 transition-colors group">
                        <div class="w-11 h-11 rounded-lg bg-success/10 dark:bg-success/20 flex items-center justify-center text-success group-hover:bg-success/20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-base-content text-center">{{ __('tenant_dashboard.action_withdraw') }}</span>
                    </a>
                    <a href="{{ route('tenant.booking-slots.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-200/50 dark:bg-base-800/50 border border-base-200/50 hover:bg-base-200/70 dark:hover:bg-base-700/50 transition-colors group">
                        <div class="w-11 h-11 rounded-lg bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary group-hover:bg-primary/20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-base-content text-center">{{ __('tenant_dashboard.action_slots') }}</span>
                    </a>
                    <a href="{{ route('tenant.packages.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-base-200/50 dark:bg-base-800/50 border border-base-200/50 hover:bg-base-200/70 dark:hover:bg-base-700/50 transition-colors group">
                        <div class="w-11 h-11 rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-base-content text-center">{{ __('tenant_dashboard.action_packages') }}</span>
                    </a>
                </div>
            </article>
        </div>
    </div>
@endsection

{{-- No @section('scripts') needed; removed Chart.js entirely --}}