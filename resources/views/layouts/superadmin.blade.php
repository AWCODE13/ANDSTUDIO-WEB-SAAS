<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ ($theme ?? 'dark') === 'light' ? 'studio-light' : 'studio-dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Superadmin') - ANDstudio Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-base-200 text-base-content antialiased flex h-screen overflow-hidden min-h-screen">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-base-100 border-r border-base-300 flex flex-col justify-between shrink-0 h-full hidden lg:flex">
        <div>
            <div class="h-24 flex items-center px-8 gap-3">
                <img src="{{ asset('images/studio.png') }}" alt="ANDstudio" class="h-10 w-auto object-contain">
                <span class="block text-[10px] uppercase tracking-widest text-primary font-semibold mt-1">{{ __('nav.superadmin') }}</span>
            </div>

            <div class="px-6 mb-3 text-xs font-bold text-base-content/50 tracking-wider">PLATFORM</div>
            <nav class="px-4 space-y-1.5">
                <a href="{{ route('superadmin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('superadmin.dashboard') ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content hover:bg-base-200 transition' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ __('nav.dashboard') }}
                </a>
                <a href="{{ route('superadmin.tenants.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('superadmin.tenants.*') ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content hover:bg-base-200 transition' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ __('nav.tenants.index') }}
                </a>
                <a href="{{ route('superadmin.tenants.create') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('superadmin.tenants.create') ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content hover:bg-base-200 transition' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ __('nav.tenants.create') }}
                </a>
                <a href="{{ route('superadmin.settings') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('superadmin.settings*') ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content hover:bg-base-200 transition' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ __('nav.settings') }}
                </a>
                <a href="{{ route('superadmin.analytics') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('superadmin.analytics*') ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content hover:bg-base-200 transition' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ __('nav.analytics') }}
                </a>
                <a href="{{ route('superadmin.withdrawals.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('superadmin.withdrawals.*') ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content hover:bg-base-200 transition' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    Pencairan
                    @php $__pendingWd = \App\Models\Withdrawal::withoutGlobalScopes()->where('status', 'pending')->count(); @endphp
                    @if ($__pendingWd > 0)
                        <span class="ml-auto px-2 py-0.5 bg-warning text-warning-content text-[10px] font-extrabold rounded-full">{{ $__pendingWd }}</span>
                    @endif
                </a>
            </nav>

            <div class="px-6 mt-6 mb-3 text-xs font-bold text-base-content/50 tracking-wider">MODERASI</div>
            <nav class="px-4 space-y-1.5">
                <a href="{{ route('superadmin.reviews.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('superadmin.reviews.*') ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content hover:bg-base-200 transition' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ __('nav.reviews.index') }}
                </a>
                <a href="{{ route('superadmin.review-requests.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('superadmin.review-requests.*') ? 'bg-primary text-primary-content' : 'text-base-content/70 hover:text-base-content hover:bg-base-200 transition' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ __('nav.review-requests.index') }}
                    @php $pendingCount = \App\Models\ReviewDeletionRequest::withoutGlobalScopes()->pending()->count(); @endphp
                    @if ($pendingCount > 0)
                        <span class="ml-auto px-2 py-0.5 bg-warning text-warning-content text-[10px] font-extrabold rounded-full">{{ $pendingCount }}</span>
                    @endif
                </a>
            </nav>
        </div>

        <div class="p-6 border-t border-base-300 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold text-primary-content">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <span class="block text-sm font-bold text-base-content truncate max-w-[110px]">{{ Auth::user()->name }}</span>
                    <span class="block text-xs text-primary">{{ __('nav.superadmin') }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-base-content/50 hover:text-error transition">{{ __('nav.logout') }}</button>
            </form>
        </div>
    </aside>

    <!-- KONTEN -->
    <div class="flex-1 flex flex-col h-full overflow-hidden min-h-0">
        <header class="h-16 sm:h-20 bg-base-100/80 backdrop-blur-md px-4 sm:px-8 flex items-center justify-between sticky top-0 z-20 border-b border-base-300">
            <div class="flex items-center gap-3">
                <!-- Hamburger Menu Toggle (Mobile) -->
                <button id="sidebar-toggle-mobile" class="lg:hidden w-10 h-10 rounded-xl bg-base-200 hover:bg-base-300 text-base-content/70 hover:text-base-content transition flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="text-xs sm:text-sm font-medium text-base-content/70 truncate">
                    {{ __('nav.superadmin') }} <span class="mx-1 sm:mx-2 text-base-content/40">&gt;</span> <span class="text-base-content font-bold truncate">@yield('page_title', 'Dashboard')</span>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-4">
                <div class="relative hidden md:block">
                    <input type="text" placeholder="{{ __('header.search.placeholder') }}" class="w-48 px-3 py-2 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                </div>
                <div class="dropdown dropdown-end">
                    <button class="w-10 h-10 rounded-xl bg-base-200 hover:bg-base-300 text-base-content/70 hover:text-base-content transition flex items-center justify-center indicator">
                        <span id="notif-badge" class="indicator-item badge badge-sm badge-primary hidden">0</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>
                    <ul id="notif-dropdown" class="dropdown-content menu bg-base-100 border border-base-300 rounded-lg shadow-lg w-80 p-0">
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
                <!-- Theme Toggle -->
                <button id="theme-toggle" class="w-10 h-10 rounded-xl bg-base-200 hover:bg-base-300 text-base-content/70 hover:text-base-content transition flex items-center justify-center">
                    <svg id="theme-icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg id="theme-icon-moon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
                <div class="hidden sm:flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 bg-base-100 border border-base-300 text-primary text-[10px] sm:text-xs font-bold rounded-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                    <span class="hidden sm:inline">{{ count(\App\Models\Tenant::where('subscription_status', 'active')->get()) }}</span> {{ __('header.active_studios') }}
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 min-h-0">
            <div class="max-w-6xl mx-auto space-y-6 sm:space-y-8">
                @if (session('success'))
                    <div class="p-4 rounded-xl bg-success/10 border border-success/20 text-success text-sm font-semibold">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 rounded-xl bg-error/10 border border-error/20 text-error text-sm font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

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

        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeIconSun = document.getElementById('theme-icon-sun');
        const themeIconMoon = document.getElementById('theme-icon-moon');

        function updateThemeIcons() {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'studio-dark';
            if (themeIconSun && themeIconMoon) {
                themeIconSun.classList.toggle('hidden', !isDark);
                themeIconMoon.classList.toggle('hidden', isDark);
            }
        }

        updateThemeIcons();

        themeToggle?.addEventListener('click', async () => {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'studio-dark';
            const nextTheme = isDark ? 'studio-light' : 'studio-dark';
            html.setAttribute('data-theme', nextTheme);
            updateThemeIcons();
            try {
                await fetch('{{ route('superadmin.settings.update') }}', {
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

        // Sidebar Mobile Toggle
        const sidebarToggle = document.getElementById('sidebar-toggle-mobile');
        const sidebar = document.querySelector('aside');

        function createSidebarOverlay() {
            const overlay = document.createElement('div');
            overlay.id = 'sidebar-overlay';
            overlay.className = 'fixed inset-0 bg-black/50 z-40 lg:hidden opacity-0 pointer-events-none transition-opacity duration-300';
            document.body.appendChild(overlay);
            return overlay;
        }

        let sidebarOverlay = document.getElementById('sidebar-overlay');
        if (!sidebarOverlay) sidebarOverlay = createSidebarOverlay();

        function openSidebar() {
            sidebar?.classList.add('lg:flex');
            sidebar?.classList.remove('hidden');
            sidebarOverlay?.classList.remove('opacity-0', 'pointer-events-none');
            sidebarOverlay?.classList.add('opacity-100', 'pointer-events-auto');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebarOverlay?.classList.remove('opacity-100', 'pointer-events-auto');
            sidebarOverlay?.classList.add('opacity-0', 'pointer-events-none');
            document.body.style.overflow = '';
            // Keep sidebar open on desktop
        }

        sidebarToggle?.addEventListener('click', () => {
            if (sidebarOverlay.classList.contains('opacity-100')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        sidebarOverlay?.addEventListener('click', closeSidebar);

        // Close sidebar on nav link click (mobile)
        document.querySelectorAll('aside a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) closeSidebar();
            });
        });

        // Responsive: ensure sidebar visible on desktop, hidden on mobile initially
        function handleResize() {
            const isDesktop = window.innerWidth >= 1024;
            if (isDesktop) {
                sidebar?.classList.remove('hidden');
                sidebarOverlay?.classList.add('opacity-0', 'pointer-events-none');
                document.body.style.overflow = '';
            } else {
                if (!sidebarOverlay.classList.contains('opacity-100')) {
                    sidebar?.classList.add('hidden');
                }
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();
    </script>
    @stack('scripts')
</body>
</html>
