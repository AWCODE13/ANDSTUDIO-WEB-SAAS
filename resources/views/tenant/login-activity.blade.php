@extends('layouts.tenant')

@section('title', 'Riwayat Login')
@section('page_title', 'Riwayat Login & Aktivitas Masuk')

@section('content')
    {{-- Header --}}
    <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content">
                {{ __('login_activity.title') }}
            </h1>
            <p class="text-base-content/60 text-sm mt-1">
                {{ __('login_activity.subtitle') }}
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('tenant.login-activity') }}" class="px-4 py-2 rounded-xl bg-base-200 dark:bg-base-800/50 text-base-content text-xs sm:text-sm font-semibold hover:bg-base-300 dark:hover:bg-base-800 transition-colors">
                {{ __('login_activity.btn_refresh') }}
            </a>
        </div>
    </div>

    {{-- Statistik Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base-content/60 text-xs font-medium mb-1">{{ __('login_activity.stat_total') }}</p>
                    <h2 class="text-2xl font-extrabold text-base-content">{{ number_format($stats['total']) }}</h2>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base-content/60 text-xs font-medium mb-1">{{ __('login_activity.stat_success') }}</p>
                    <h2 class="text-2xl font-extrabold text-success">{{ number_format($stats['success']) }}</h2>
                </div>
                <div class="w-12 h-12 rounded-xl bg-success/10 dark:bg-success/20 flex items-center justify-center text-success">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base-content/60 text-xs font-medium mb-1">{{ __('login_activity.stat_failed') }}</p>
                    <h2 class="text-2xl font-extrabold text-error">{{ number_format($stats['failed']) }}</h2>
                </div>
                <div class="w-12 h-12 rounded-xl bg-error/10 dark:bg-error/20 flex items-center justify-center text-error">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base-content/60 text-xs font-medium mb-1">{{ __('login_activity.stat_last_login') }}</p>
                    <h2 class="text-2xl font-extrabold text-base-content">
                        {{ $stats['last_login'] ? \Carbon\Carbon::parse($stats['last_login'])->translatedFormat('d M Y H:i') : __('login_activity.never') }}
                    </h2>
                </div>
                <div class="w-12 h-12 rounded-xl bg-base-200 dark:bg-base-800/50 flex items-center justify-center text-base-content/50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </article>
    </div>

    {{-- Filter & Tabel --}}
    <article class="rounded-2xl border border-base-200 dark:border-base-700/60 bg-base-100 dark:bg-base-900 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-base-200 dark:border-base-700/50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h3 class="text-lg font-bold text-base-content">{{ __('login_activity.table_title') }}</h3>

                <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <select name="status" class="w-full sm:w-auto px-3 py-2 rounded-xl bg-base-200/50 border border-base-300 text-base-content text-sm focus:outline-none focus:border-primary">
                        <option value="">{{ __('login_activity.filter_all_status') }}</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>{{ __('login_activity.filter_success') }}</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('login_activity.filter_failed') }}</option>
                    </select>

                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full sm:w-auto px-3 py-2 rounded-xl bg-base-200/50 border border-base-300 text-base-content text-sm focus:outline-none focus:border-primary"
                        placeholder="{{ __('login_activity.filter_date_from') }}">

                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full sm:w-auto px-3 py-2 rounded-xl bg-base-200/50 border border-base-300 text-base-content text-sm focus:outline-none focus:border-primary"
                        placeholder="{{ __('login_activity.filter_date_to') }}">

                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors">
                        {{ __('common.filter') }}
                    </button>

                    <a href="{{ route('tenant.login-activity') }}" class="px-4 py-2 rounded-xl bg-base-200 dark:bg-base-800/50 text-base-content text-sm font-semibold hover:bg-base-300 dark:hover:bg-base-800 transition-colors">
                        {{ __('login_activity.btn_reset') }}
                    </a>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" role="table" aria-label="{{ __('login_activity.aria_table') }}">
                <thead class="bg-base-200/50 text-base-content/60 font-semibold border-b border-base-200 dark:border-base-700/50">
                    <tr>
                        <th class="px-5 py-3">{{ __('login_activity.table_time') }}</th>
                        <th class="px-5 py-3">{{ __('login_activity.table_user') }}</th>
                        <th class="px-5 py-3">{{ __('login_activity.table_ip') }}</th>
                        <th class="px-5 py-3">{{ __('login_activity.table_device') }}</th>
                        <th class="px-5 py-3">{{ __('login_activity.table_status') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('login_activity.table_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200/50 dark:divide-base-700/50">
                    @forelse ($activities as $activity)
                        <tr class="{{ $activity->status === 'failed' ? 'bg-error/5' : '' }} hover:bg-base-200/50 dark:hover:bg-base-800/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-medium text-base-content">{{ \Carbon\Carbon::parse($activity->login_at)->format('d M Y') }}</div>
                                <div class="text-xs text-base-content/50">{{ \Carbon\Carbon::parse($activity->login_at)->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-base-content truncate">{{ $activity->user?->name ?? '-' }}</div>
                                <div class="text-xs text-base-content/50">{{ $activity->user?->email ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-base-content/70">
                                {{ $activity->ip_address ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-base-content truncate max-w-xs">
                                    {{ $activity->parseUserAgent() ?? '-' }}
                                </div>
                                <div class="text-xs text-base-content/50 truncate max-w-xs">
                                    {{ $activity->parseDeviceType() ?? '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                                    {{ $activity->status === 'success'
                                        ? 'bg-success/10 text-success border border-success/20'
                                        : 'bg-error/10 text-error border border-error/20' }}">
                                    {{ $activity->status === 'success' ? __('login_activity.status_success') : __('login_activity.status_failed') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right text-base-content/50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-base-content/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    <p class="font-medium text-base-content/60">{{ __('login_activity.empty') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-5 py-4 border-t border-base-200 dark:border-base-700/50">
            {{ $activities->links() }}
        </div>
    </article>
@endsection
