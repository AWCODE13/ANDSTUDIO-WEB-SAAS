@extends('layouts.tenant')

@section('title', 'Data & Riwayat Pelanggan')
@section('page_title', 'Data & Riwayat Pelanggan')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-base-content">Data & Riwayat Pelanggan</h1>
            <p class="text-base-content/60 text-sm mt-1">Direktori pelanggan studio, rekap reservasi, dan total transaksi yang sudah dibayarkan.</p>
        </div>
        <form method="GET" action="{{ route('tenant.customers.index') }}" class="flex items-center gap-2">
            <div class="relative">
                <input type="text" name="q" value="{{ $search ?? '' }}"
                    placeholder="Cari nama, WA, email..."
                    class="w-64 sm:w-72 px-3 py-2 pl-9 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-xl outline-none text-sm">
                <svg class="w-4 h-4 text-base-content/50 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            @if($search)
                <a href="{{ route('tenant.customers.index') }}" class="px-3 py-2 bg-base-200 hover:bg-base-300 text-xs font-semibold rounded-xl text-base-content">Reset</a>
            @endif
        </form>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-gray-200 dark:bg-white/5 dark:backdrop-blur-md dark:border-white/10 shadow-sm">
            <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider block">Total Pelanggan</span>
            <span class="text-2xl font-extrabold text-base-content mt-1 block">{{ number_format($stats['total_customers'], 0, ',', '.') }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-gray-200 dark:bg-white/5 dark:backdrop-blur-md dark:border-white/10 shadow-sm">
            <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider block">Pelanggan Repeat (Loyal)</span>
            <span class="text-2xl font-extrabold text-emerald-500 mt-1 block">{{ number_format($stats['repeat_customers'], 0, ',', '.') }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-gray-200 dark:bg-white/5 dark:backdrop-blur-md dark:border-white/10 shadow-sm">
            <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider block">Total Nilai Transaksi Terverifikasi</span>
            <span class="text-2xl font-extrabold text-success mt-1 block">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Tabel Pelanggan -->
    <div class="bg-white rounded-2xl border border-gray-200 dark:bg-white/5 dark:backdrop-blur-md dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Pelanggan</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kontak (WhatsApp & Email)</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Reservasi</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Dibelanjakan</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Order Terakhir</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                @forelse($customers as $customer)
                    @php
                        $history = $customer['booking_history'] ?? $customer['bookings'] ?? [];
                    @endphp
                    <tbody x-data="{ open: false }" class="bg-white divide-y divide-gray-200 dark:bg-transparent dark:divide-gray-700">
                        <!-- Baris Utama -->
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-base-content flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 flex items-center justify-center font-bold text-xs uppercase">
                                        {{ substr($customer['name'], 0, 2) }}
                                    </div>
                                    <span>{{ $customer['name'] }}</span>
                                    @if($customer['total_bookings'] > 1)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                            Repeat ({{ $customer['total_bookings'] }}x)
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer['phone']) }}" target="_blank" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span>{{ $customer['phone'] }}</span>
                                    </a>
                                    <span class="text-sm text-base-content/60 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span>{{ $customer['email'] }}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-medium bg-base-200 text-base-content">
                                    {{ $customer['total_bookings'] }} Pesanan
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-emerald-500 dark:text-emerald-400 text-sm">
                                    Rp {{ number_format($customer['total_spent'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-base-content/70 text-xs">
                                @if($customer['last_booking_date'])
                                    <div>{{ $customer['last_booking_date']->format('d M Y') }}</div>
                                    <div class="text-[11px] text-base-content/50">{{ $customer['last_booking_date']->diffForHumans() }}</div>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-600 rounded-full text-sm leading-4 font-medium text-gray-300 hover:text-white hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    Riwayat
                                    <svg :class="{'rotate-180': open}" class="h-4 w-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <!-- Baris Detail Riwayat -->
                        <tr x-show="open" style="display: none;" class="bg-gray-50 dark:bg-gray-900/50">
                            <td colspan="6" class="px-6 py-4">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                    @if(count($history) > 0)
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-100 dark:bg-gray-800">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Waktu</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kode</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Paket</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach($history as $booking)
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-300">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : '' }} {{ substr($booking->start_time ?? '', 0, 5) }} - {{ substr($booking->end_time ?? '', 0, 5) }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-300">{{ $booking->booking_code }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-300">{{ $booking->package->name ?? '-' }}</td>
                                                        <td class="px-4 py-2 text-sm text-emerald-500 font-medium">Rp {{ number_format($booking->total_price ?? $booking->total_amount, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="p-4 text-sm text-gray-500 text-center">Belum ada riwayat pesanan.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-base-content/50">
                                <p class="font-medium text-sm">Belum ada data pelanggan.</p>
                                <p class="text-xs mt-1">Semua reservasi online maupun manual yang masuk akan otomatis tercatat di sini.</p>
                            </td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>
    </div>
@endsection
