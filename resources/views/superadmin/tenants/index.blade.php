@extends('layouts.superadmin')

@section('title', 'Semua Studio')
@section('page_title', 'Semua Studio')

@section('content')
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Semua Studio</h1>
            <p class="text-base-content/50 text-sm mt-1">Kelola tenant, paket langganan, dan status pembayaran platform.</p>
        </div>
        <a href="{{ route('superadmin.tenants.create') }}"
           class="btn btn-primary btn-sm border-0 sm:px-5 shadow-md shadow-sky-600/20">
            + Tambah Studio
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        @foreach([
            ['Total Studio', $stats['total'], 'text-base-content', '+3.2%'],
            ['Plan Pro', $stats['pro'], 'text-success', '+8.4%'],
            ['Partner', $stats['partner'], 'text-accent', '+2.1%'],
            ['Aktif', $stats['active'], 'text-primary', '+5.6%'],
        ] as $stat)
            <div class="bg-base-100 rounded-xl border border-base-300 overflow-hidden">
                <div class="stat">
                    <div class="stat-title text-[11px] sm:text-xs font-semibold text-base-content/60">{{ $stat[0] }}</div>
                    <div class="stat-value text-xl sm:text-2xl font-extrabold {{ $stat[2] }}">{{ $stat[1] }}</div>
                    <div class="stat-desc flex items-center gap-1.5 mt-1">
                        <span class="text-success font-bold text-xs">↑ {{ $stat[3] }}</span>
                        <span class="text-[10px] text-base-content/60">vs bulan lalu</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('superadmin.tenants.index') }}"
          class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-5 mb-6 flex flex-col sm:flex-row gap-2 sm:gap-3">
        <div class="flex-1">
            <input type="text" name="q" value="{{ $query }}" placeholder="Cari studio..."
                   class="w-full px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
        </div>
        <select name="plan" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
            <option value="">Semua Plan</option>
            <option value="free" @selected($plan === 'free')>Free</option>
            <option value="pro" @selected($plan === 'pro')>Pro</option>
        </select>
        <select name="status" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
            <option value="">Semua Status</option>
            <option value="active" @selected($status === 'active')>Aktif</option>
            <option value="past_due" @selected($status === 'past_due')>Tagihan Terlambat</option>
            <option value="canceled" @selected($status === 'canceled')>Nonaktif</option>
        </select>
        <button type="submit" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-primary hover:bg-primary/90 text-white text-xs sm:text-sm font-semibold rounded-xl transition">
            Filter
        </button>
        @if ($query || $plan || $status)
            <a href="{{ route('superadmin.tenants.index') }}" class="px-3 py-2 sm:py-2.5 min-h-[40px] bg-base-200 hover:bg-base-300 text-base-content/70 text-xs sm:text-sm font-semibold rounded-xl transition">
                Reset
            </a>
        @endif
    </form>

    <!-- Desktop: Tabel -->
    <div class="hidden lg:block bg-base-100 rounded-2xl border border-base-300 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm [&_thead]:text-base-content/60 [&_thead]:bg-base-200 [&_th]:text-[11px] [&_th]:uppercase [&_th]:tracking-wider [&_th]:border-0 [&_td]:border-t-0 [&_td]:border-b [&_td]:border-base-300 [&_tr:hover]:bg-base-200/80">
                <thead>
                    <tr>
                        <th>Studio</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th class="text-center">Paket</th>
                        <th class="text-center">Booking</th>
                        <th class="text-center">Tim</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-base-content/70">
                    @forelse ($tenants as $tenant)
                        @php $label = $tenant->subscriptionLabel(); @endphp
                        <tr class="transition">
                            <td class="border-b border-base-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-primary flex items-center justify-center text-base-content font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block font-bold text-base-content truncate">{{ $tenant->name }}</span>
                                        <span class="block text-xs text-base-content/60 truncate">{{ $tenant->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="border-b border-base-300">
                                <span class="inline-block px-2 py-1 rounded-md text-[10px] font-bold uppercase {{ $tenant->subscription_plan === 'pro' ? 'bg-emerald-500/10 text-success' : 'bg-base-300 text-base-content/50' }}">
                                    {{ $tenant->subscription_plan }}
                                </span>
                            </td>
                            <td class="border-b border-base-300">
                                @php
                                    $statusMap = ['active' => ['bg-emerald-500/10 text-success', 'Aktif'],
                                                  'past_due' => ['bg-amber-500/10 text-warning', 'Terlambat'],
                                                  'canceled' => ['bg-rose-500/10 text-rose-400', 'Nonaktif']];
                                    $stat = $statusMap[$tenant->subscription_status] ?? ['bg-base-300 text-base-content/50', $label['text']];
                                @endphp
                                <span class="inline-block px-2 py-1 rounded-md text-[10px] font-bold uppercase {{ $stat[0] }}">
                                    {{ $stat[1] }}
                                </span>
                            </td>
                            <td class="border-b border-base-300 text-center font-semibold text-base-content/70">{{ $tenant->packages_count }}</td>
                            <td class="border-b border-base-300 text-center font-semibold text-base-content/70">{{ $tenant->bookings_count }}</td>
                            <td class="border-b border-base-300 text-center font-semibold text-base-content/70">{{ $tenant->users_count }}</td>
                            <td class="border-b border-base-300 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ config('app.public_url') }}/studio/{{ $tenant->slug }}" target="_blank" rel="noopener"
                                       class="btn btn-xs border-0 bg-base-300 text-base-content/70 hover:bg-base-300">
                                        ↗ Web
                                    </a>
                                    <a href="{{ route('superadmin.tenants.show', $tenant->id) }}"
                                       class="btn btn-xs btn-primary border-0 shadow-none">
                                        Kelola
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-base-content/60">
                                Tidak ada studio yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tenants->hasPages())
            <div class="px-5 py-4 border-t border-base-300">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile: Cards -->
    <div class="lg:hidden space-y-3">
        @forelse ($tenants as $tenant)
            @php $label = $tenant->subscriptionLabel(); @endphp
            <div class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-5 space-y-3">
                <!-- Header: Nama & Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primary flex items-center justify-center text-white font-bold text-sm shrink-0">
                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="block font-bold text-base-content truncate">{{ $tenant->name }}</span>
                        <span class="block text-xs text-base-content/60 truncate">{{ $tenant->email }}</span>
                    </div>
                </div>

                <!-- Plan & Status Badges -->
                <div class="flex gap-2 flex-wrap">
                    <span class="inline-block px-2 py-1 rounded-md text-[10px] font-bold uppercase {{ $tenant->subscription_plan === 'pro' ? 'bg-success/10 text-success' : 'bg-base-300 text-base-content/60' }}">
                        {{ $tenant->subscription_plan }}
                    </span>
                    @php
                        $statusMap = ['active' => ['bg-success/10 text-success', 'Aktif'],
                                      'past_due' => ['bg-warning/10 text-warning', 'Terlambat'],
                                      'canceled' => ['bg-error/10 text-error', 'Nonaktif']];
                        $stat = $statusMap[$tenant->subscription_status] ?? ['bg-base-300 text-base-content/60', $label['text']];
                    @endphp
                    <span class="inline-block px-2 py-1 rounded-md text-[10px] font-bold uppercase {{ $stat[0] }}">
                        {{ $stat[1] }}
                    </span>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-3 gap-2 text-center py-2 border-t border-b border-base-300/50">
                    <div>
                        <span class="block text-xs font-bold text-base-content">{{ $tenant->packages_count }}</span>
                        <span class="block text-[10px] text-base-content/60">Paket</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-base-content">{{ $tenant->bookings_count }}</span>
                        <span class="block text-[10px] text-base-content/60">Booking</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-base-content">{{ $tenant->users_count }}</span>
                        <span class="block text-[10px] text-base-content/60">Tim</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <a href="{{ config('app.public_url') }}/studio/{{ $tenant->slug }}" target="_blank" rel="noopener"
                       class="flex-1 px-3 py-2 min-h-[40px] bg-base-200 hover:bg-base-300 text-base-content/70 text-xs font-semibold rounded-xl transition flex items-center justify-center">
                        ↗ Web
                    </a>
                    <a href="{{ route('superadmin.tenants.show', $tenant->id) }}"
                       class="flex-1 px-3 py-2 min-h-[40px] bg-primary hover:bg-primary/90 text-white text-xs font-semibold rounded-xl transition flex items-center justify-center">
                        Kelola
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-base-100 p-12 text-center rounded-2xl border border-base-300">
                <p class="text-base-content/60 font-medium">Tidak ada studio yang cocok dengan filter.</p>
            </div>
        @endforelse

        @if ($tenants->hasPages())
            <div class="py-4">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
@endsection
