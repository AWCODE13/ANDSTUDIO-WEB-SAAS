@extends('layouts.superadmin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-6 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-base-content">{{ __('dashboard.title') }}</h1>
            <p class="text-base-content/60 text-xs sm:text-sm mt-1">{{ __('dashboard.subtitle') }}</p>
        </div>
        @if ($stats['pending_requests'] > 0)
            <a href="{{ route('superadmin.review-requests.index') }}"
               class="btn btn-warning btn-sm px-4 sm:px-5 border-0 text-base-content shadow-md shadow-amber-500/20 shrink-0">
                {{ $stats['pending_requests'] }} {{ __('dashboard.pending_requests') }}
            </a>
        @endif
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
        @foreach([
            [__('dashboard.stat_total_studio'), $stats['tenants'], '+10.8%', 'text-base-content', 'text-success', 'bg-primary/10 text-primary'],
            [__('dashboard.stat_active_studio'), $stats['active_tenants'], '+4.2%', 'text-primary', 'text-success', 'bg-success/10 text-success'],
            [__('dashboard.stat_bookings_today'), $stats['bookings_today'], '+12.5%', 'text-base-content', 'text-success', 'bg-accent/10 text-accent'],
            [__('dashboard.stat_total_reviews'), $stats['reviews'], '+7.1%', 'text-accent', 'text-success', 'bg-warning/10 text-warning'],
        ] as $stat)
            <div class="bg-base-100 rounded-xl border border-base-300 overflow-hidden">
                <div class="stat p-3 sm:p-4">
                    <div class="stat-title text-[11px] sm:text-xs font-semibold text-base-content/60">{{ $stat[0] }}</div>
                    <div class="stat-value text-xl sm:text-2xl font-extrabold {{ $stat[3] }}">{{ $stat[1] }}</div>
                    <div class="stat-desc flex items-center gap-1.5 mt-1">
                        <span class="{{ $stat[4] }} font-bold text-[10px] sm:text-xs">↑ {{ $stat[2] }}</span>
                        <span class="text-[9px] sm:text-[10px] text-base-content/60">{{ __('dashboard.stat_vs_last_month') }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Permintaan hapus -->
        <div class="bg-base-100 rounded-2xl border border-base-300 overflow-hidden">
            <div class="px-6 py-4 border-b border-base-300 flex items-center justify-between">
                <h2 class="text-base font-bold text-base-content">{{ __('superadmin_dashboard.section.delete_requests') }}</h2>
                <a href="{{ route('superadmin.review-requests.index') }}" class="text-xs font-bold text-primary hover:text-primary transition">
                    {{ __('review_requests.btn_view_all') }} →
                </a>
            </div>

            @if ($pendingRequests->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-base-content/60">{{ __('review_requests.empty') }}</div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra table-sm [&_thead]:text-base-content/60 [&_thead]:bg-base-200 [&_th]:text-[11px] [&_th]:uppercase [&_th]:tracking-wider [&_th]:border-0 [&_td]:border-t-0 [&_td]:border-b [&_td]:border-base-300 [&_tr:hover]:bg-base-200/80 [&_.zebra-row]:bg-base-200">
                        <thead>
                            <tr>
                                <th>{{ __('review_requests.table.studio') }}</th>
                                <th>{{ __('review_requests.table.reason') }}</th>
                                <th class="text-right">{{ __('review_requests.table.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-base-content/70">
                            @foreach ($pendingRequests as $req)
                                <tr>
                                    <td class="border-b border-base-300">
                                        <div class="font-bold text-base-content text-sm">{{ $req->tenant?->name }}</div>
                                        <span class="text-warning text-xs">★ {{ $req->review?->rating }}</span>
                                    </td>
                                    <td class="border-b border-base-300 max-w-[220px]">
                                        <p class="text-xs text-base-content/50 leading-relaxed">{{ \Illuminate\Support\Str::limit($req->reason, 100) }}</p>
                                    </td>
                                    <td class="border-b border-base-300 text-right">
                                        <a href="{{ route('superadmin.review-requests.index') }}"
                                           class="text-xs font-bold text-primary hover:text-primary transition">{{ __('superadmin_dashboard.btn_process') }} →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Ulasan terbaru -->
        <div class="bg-base-100 rounded-2xl border border-base-300 overflow-hidden">
            <div class="px-6 py-4 border-b border-base-300 flex items-center justify-between">
                <h2 class="text-base font-bold text-base-content">{{ __('superadmin_dashboard.section.recent_reviews') }}</h2>
                <a href="{{ route('superadmin.reviews.index') }}" class="text-xs font-bold text-primary hover:text-primary transition">
                    {{ __('superadmin_dashboard.btn_manage_reviews') }} →
                </a>
            </div>

            @if ($recentReviews->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-base-content/60">{{ __('reviews.empty') }}</div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra table-sm [&_thead]:text-base-content/60 [&_thead]:bg-base-200 [&_th]:text-[11px] [&_th]:uppercase [&_th]:tracking-wider [&_th]:border-0 [&_td]:border-t-0 [&_td]:border-b [&_td]:border-base-300 [&_tr:hover]:bg-base-200/80">
                        <thead>
                            <tr>
                                <th>{{ __('reviews.table.studio') }}</th>
                                <th>{{ __('reviews.table.comment') }}</th>
                                <th class="text-right">{{ __('reviews.table.rating') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-base-content/70">
                            @foreach ($recentReviews as $review)
                                <tr>
                                    <td class="border-b border-base-300">
                                        <div class="font-bold text-base-content text-sm">{{ $review->tenant?->name }}</div>
                                        <div class="text-[10px] text-base-content/60">{{ $review->maskedName() }} · {{ $review->created_at?->translatedFormat('d M Y') }}</div>
                                    </td>
                                    <td class="border-b border-base-300 max-w-[220px]">
                                        @if ($review->comment)
                                            <p class="text-xs text-base-content/50 leading-relaxed">{{ \Illuminate\Support\Str::limit($review->comment, 90) }}</p>
                                        @else
                                            <span class="text-xs text-base-content/70 italic">{{ __('reviews.no_comment') }}</span>
                                        @endif
                                    </td>
                                    <td class="border-b border-base-300 text-right">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-500/10 text-warning text-xs font-bold">
                                            ★ {{ $review->rating }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
