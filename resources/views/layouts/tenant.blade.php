<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ ($theme ?? 'dark') === 'light' ? 'studio-light' : 'studio-dark' }}" class="{{ ($theme ?? 'dark') === 'light' ? '' : 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Studio Console') - {{ Auth::user()->tenant->name ?? 'ANDstudio' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Theme persistence script - runs before body to prevent flash -->
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('tenant-theme');
                var isDark = savedTheme ? (savedTheme === 'dark') : (document.documentElement.getAttribute('data-theme') !== 'studio-light');
                document.documentElement.setAttribute('data-theme', isDark ? 'studio-dark' : 'studio-light');
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media (max-width: 639px) {
            #notif-dropdown {
                position: fixed !important;
                left: 0.75rem !important;
                right: 0.75rem !important;
                top: 4.5rem !important;
                width: auto !important;
                max-width: calc(100vw - 1.5rem) !important;
                margin: 0 !important;
                transform: none !important;
            }
        }
        @media (min-width: 640px) {
            #notif-dropdown {
                position: absolute !important;
                right: 0 !important;
                left: auto !important;
                top: 100% !important;
                width: 22rem !important;
            }
        }
    </style>
</head>
<body class="bg-base-200 text-base-content antialiased flex h-screen min-h-screen">
    <!-- SIDEBAR KIRI -->
    <aside id="sidebar" class="w-64 bg-base-100 border-r border-base-300 flex flex-col justify-between md:flex shrink-0 h-full fixed md:static inset-y-0 left-0 z-50 -translate-x-full md:translate-x-0 transition-transform duration-300">
        <div class="flex-1 overflow-y-auto">
            <div class="h-24 flex items-center px-8 gap-3">
                <img src="{{ asset('images/studio.png') }}" alt="Studio" class="h-10 w-auto object-contain">
            </div>
            <div class="px-6 mb-3 text-xs font-bold text-base-content/50 tracking-wider">MENU</div>
            <nav class="px-4 space-y-1.5">
                <a href="{{ route('tenant.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.dashboard') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.tenant.dashboard') }}
                </a>
                <a href="{{ route('tenant.packages.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.packages.*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.tenant.packages.index') }}
                </a>
                <a href="{{ route('tenant.booking-slots.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.booking-slots.*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.tenant.booking-slots.index') }}
                </a>
                <a href="{{ route('tenant.expenses.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.expenses.*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.tenant.expenses.index') }}
                </a>
                <a href="{{ route('tenant.bookings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.bookings.*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.tenant.bookings.index') }}
                </a>
                <a href="{{ route('tenant.customers.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.customers.*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    Pelanggan
                </a>
                <a href="{{ route('tenant.reviews.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.reviews.*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.tenant.reviews.index') }}
                    @php $__pendingReviews = \App\Models\ReviewDeletionRequest::withoutGlobalScopes()->where('tenant_id', Auth::user()->tenant_id)->pending()->count(); @endphp
                    @if ($__pendingReviews > 0)
                        <span class="ml-auto px-1.5 py-0.5 rounded text-[9px] font-bold bg-warning text-warning-content">{{ $__pendingReviews }}</span>
                    @endif
                </a>
                <a href="{{ route('tenant.billing.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.billing.*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.tenant.billing.index') }}
                    @php $__label = Auth::user()->tenant?->subscriptionLabel() ?? ['text' => 'Free', 'color' => 'bg-base-200 text-base-content/60']; @endphp
                    <span class="ml-auto px-1.5 py-0.5 rounded text-[9px] font-bold {{ $__label['color'] }}">{{ $__label['text'] }}</span>
                </a>
                <a href="{{ route('tenant.withdrawals.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.withdrawals.*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    Tarik Saldo
                    @php $__pendingW = Auth::user()->tenant?->withdrawals()->withoutGlobalScopes()->where('status', 'pending')->count(); @endphp
                    @if ($__pendingW > 0)
                        <span class="ml-auto px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500 text-white">1 Pending</span>
                    @endif
                </a>
                <a href="{{ route('tenant.profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.profile.*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.tenant.profile.edit') }}
                </a>
                <a href="{{ route('tenant.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.settings*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.settings') }}
                </a>
                <a href="{{ route('tenant.analytics') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.analytics*') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.analytics') }}
                </a>
                <a href="{{ route('tenant.login-activity') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('tenant.login-activity') ? 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5 dark:hover:backdrop-blur-sm transition-all duration-300' }}">
                    {{ __('nav.login_activity') }}
                    <svg class="ml-auto w-4 h-4 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </a>
            </nav>
        </div>
        <div class="p-6 border-t border-base-300 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center font-bold text-primary">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <span class="block text-sm font-bold text-base-content">{{ Auth::user()->name }}</span>
                    <span class="block text-xs text-base-content/60">Administrator</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-base-content/50 hover:text-error transition">{{ __('nav.logout') }}</button>
            </form>
        </div>
    </aside>

    <!-- AREA KONTEN UTAMA -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-20 bg-base-100/80 backdrop-blur-md px-4 md:px-8 flex items-center justify-between sticky top-0 z-20 border-b border-base-300">
            <div class="flex items-center gap-4">
                <button id="hamburger" class="md:hidden text-base-content/70 hover:text-base-content">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="text-sm font-medium text-base-content/70">
                    Home <span class="mx-2"></span> <span class="text-base-content font-bold">@yield('page_title', 'Dashboard')</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="dropdown dropdown-end">
                    <button class="btn btn-ghost btn-circle text-base-content/70 hover:text-base-content hover:bg-base-200 indicator">
                        <span id="notif-badge" class="indicator-item badge badge-sm badge-primary hidden">0</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>
                    <ul id="notif-dropdown" class="dropdown-content menu bg-base-100 border border-base-300 rounded-2xl shadow-2xl p-0 z-50 overflow-hidden">
                        <li class="px-4 py-3 border-b border-base-300">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm">Notifikasi</span>
                                <button id="mark-all-read-btn" class="text-xs text-primary hover:underline hidden">Tandai semua</button>
                            </div>
                        </li>
                        <div id="notif-list" class="max-h-96 overflow-y-auto">
                            <li class="px-4 py-8 text-center text-base-content/50 text-sm">Memuat notifikasi...</li>
                        </div>
                        <li class="border-t border-base-300 px-4 py-2">
                            <a href="#" class="text-xs text-primary text-center justify-center">Lihat semua notifikasi</a>
                        </li>
                    </ul>
                </div>
                <button id="theme-toggle" class="btn btn-ghost btn-circle text-base-content/70 hover:text-base-content hover:bg-base-200">
                    <svg id="theme-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                @if(Auth::user()->tenant?->slug)
                    <a href="{{ config('app.public_url') }}/studio/{{ Auth::user()->tenant->slug }}" target="_blank" rel="noopener" class="btn btn-ghost btn-circle btn-sm text-base-content/70 hover:text-base-content hover:bg-base-200" title="{{ __('header.view_public_web') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endif
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="max-w-6xl mx-auto space-y-6">
                @if (session('success'))
                    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                @php
                    $__tenant = Auth::user()->tenant;
                @endphp

                @if ($__tenant && $__tenant->isTrialActive())
                    <div class="p-4 rounded-xl bg-warning/10 border border-warning/20 text-base-content text-sm flex items-center justify-between gap-4">
                        <span>
                            <strong>{{ __('tenant_banner.trial', ['days' => $__tenant->trialDaysLeft()]) }}</strong>
                            {{ __('tenant_banner.trial_desc') }}
                        </span>
                        <a href="{{ route('tenant.billing.index') }}" class="shrink-0 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] transition-all duration-300 px-4 py-2 rounded-lg text-xs font-bold">
                            {{ __('tenant_banner.view_plan') }}
                        </a>
                    </div>
                @elseif ($__tenant && $__tenant->isPastDue())
                    <div class="p-4 rounded-xl bg-error/10 border border-error/20 text-base-content text-sm flex items-center justify-between gap-4">
                        <span>
                            <strong>{{ __('tenant_banner.overdue') }}</strong>
                            {{ __('tenant_banner.overdue_desc') }}
                        </span>
                        <a href="{{ route('tenant.billing.index') }}" class="shrink-0 bg-error text-error-content px-4 py-2 rounded-lg text-xs font-bold transition">
                            {{ __('tenant_banner.pay') }}
                        </a>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Overlay mobile -->
    <div id="overlay" class="fixed inset-0 bg-slate-900/50 z-40 md:hidden hidden"></div>
    
    <script>
        // Notification Polling
        function loadNotifications() {
            fetch('/notifications')
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('notif-badge');
                    const list = document.getElementById('notif-list');
                    const markAllBtn = document.getElementById('mark-all-read-btn');

                    badge.textContent = data.count || 0;
                    if (data.count > 0) {
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }

                    if (data.notifications?.length > 0) {
                        markAllBtn.classList.remove('hidden');
                        list.innerHTML = data.notifications.map(n => `
                            <li class="px-4 py-3 border-b border-base-300 cursor-pointer hover:bg-base-200 transition ${n.is_read ? 'opacity-60' : 'font-semibold'}" onclick="markNotifRead('${n.id}')">
                                <div class="flex items-start gap-3">
                                    <div class="text-xl mt-1">
                                        ${n.icon === 'calendar' ? '📅' : n.icon === 'star' ? '⭐' : n.icon === 'shield' ? '🛡️' : '🔔'}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold">${n.title}</p>
                                        <p class="text-xs text-base-content/60 mt-1">${n.message}</p>
                                        <p class="text-[11px] text-base-content/40 mt-1">${n.created_at}</p>
                                    </div>
                                </div>
                            </li>
                        `).join('');
                    } else {
                        markAllBtn.classList.add('hidden');
                        list.innerHTML = '<li class="px-4 py-8 text-center text-base-content/50 text-sm">Tidak ada notifikasi</li>';
                    }
                })
                .catch(e => console.warn('Failed to load notifications:', e));
        }

        function markNotifRead(id) {
            fetch(`/notifications/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(() => loadNotifications())
                .catch(e => console.warn('Failed to mark notification:', e));
        }

        document.getElementById('mark-all-read-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            fetch('/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(() => loadNotifications())
                .catch(e => console.warn('Failed to mark all:', e));
        });

        loadNotifications();
        setInterval(loadNotifications, 30000);

        // Mobile Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const hamburger = document.getElementById('hamburger');
        const overlay = document.getElementById('overlay');
        
        hamburger?.addEventListener('click', () => {
            const isOpening = !sidebar.classList.contains('-translate-x-full');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            // Kunci scroll body saat drawer terbuka, biarkan drawer sendiri di-scroll
            document.body.style.overflow = isOpening ? 'hidden' : '';
        });
        
        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        });

        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        function updateThemeIcon(isDark) {
            if (!themeIcon) return;
            if (isDark) {
                themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
            } else {
                themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
            }
        }
        updateThemeIcon(document.documentElement.getAttribute('data-theme') === 'studio-dark');
        themeToggle?.addEventListener('click', async () => {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'studio-dark';
            const nextTheme = isDark ? 'studio-light' : 'studio-dark';
            html.setAttribute('data-theme', nextTheme);
            html.classList.toggle('dark', nextTheme === 'studio-dark');
            localStorage.setItem('tenant-theme', nextTheme === 'studio-dark' ? 'dark' : 'light');
            updateThemeIcon(!isDark);
            try {
                await fetch('{{ route('tenant.settings.update') }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        theme: nextTheme === 'studio-dark' ? 'dark' : 'light',
                        locale: '{{ $userLocale }}',
                        country: '{{ $userCountry }}',
                    }),
                });
            } catch (e) {
                // diam saja; tema tetap berubah di UI
            }
        });

        // Notification Dropdown Toggle
        const notifTrigger = document.querySelector('.dropdown.dropdown-end');
        const notifBtn = notifTrigger?.querySelector(':scope > button');
        notifBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            notifTrigger?.classList.toggle('dropdown-open');
        });
        document.addEventListener('click', (e) => {
            if (notifTrigger && !notifTrigger.contains(e.target)) {
                notifTrigger.classList.remove('dropdown-open');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>