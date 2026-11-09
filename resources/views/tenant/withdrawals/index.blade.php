@extends('layouts.tenant')

@section('title', 'Tarik Saldo')
@section('page_title', 'Tarik Saldo')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl md:text-2xl font-bold text-base-content">Tarik Saldo</h1>
        <p class="text-base-content/60 text-sm mt-1">Kelola pencairan pendapatan studio Anda ke rekening bank.</p>
    </div>

    {{-- Status & Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Saldo Tersedia --}}
        <div class="bg-base-100 border border-base-300 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="block text-xs text-base-content/70">Saldo Tersedia</span>
                    <span class="block text-2xl font-bold text-emerald-500">{{ 'Rp ' . number_format($availableBalance, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Total Ditarik --}}
        <div class="bg-base-100 border border-base-300 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 dark:bg-sky-500/20 text-sky-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <span class="block text-xs text-base-content/70">Total Diterima</span>
                    <span class="block text-2xl font-bold text-sky-500">{{ 'Rp ' . number_format($totalWithdrawn, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Status Rekening --}}
        <div class="bg-base-100 border border-base-300 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl {{ $hasBank ? 'bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-500' : 'bg-warning/10 text-warning' }} flex items-center justify-center shrink-0">
                    @if($hasBank)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @endif
                </div>
                <div>
                    <span class="block text-xs text-base-content/70">Rekening Bank</span>
                    <span class="block text-sm font-semibold text-base-content">
                        @if($hasBank)
                            {{ Auth::user()->tenant->bank_name }} ({{ Auth::user()->tenant->account_number }})
                        @else
                            <span class="text-warning">Belum dikonfigurasi</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Ajukan Penarikan --}}
    @if($hasBank)
        <div class="bg-base-100 border border-base-300 rounded-2xl p-5 mb-6 shadow-sm">
            <h2 class="text-base font-bold text-base-content mb-4">Ajukan Penarikan Baru</h2>

            @if($errors->any())
                <div class="mb-4 p-4 bg-error/10 border border-error/20 text-error text-sm font-semibold rounded-xl">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('tenant.withdrawals.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="amount">Nominal Penarikan</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-600 bg-gray-700 text-gray-400 sm:text-sm">
                            Rp
                        </span>
                        <input type="number" name="amount" id="amount" value="{{ old('amount') }}"
                            min="50000"
                            max="{{ $availableBalance }}"
                            placeholder="50000"
                            class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md bg-transparent border border-gray-600 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm dark:text-white"
                            required>
                    </div>
                    <p class="text-xs text-base-content/60 mt-1.5">
                        Minimal Rp50.000. Maksimal <strong>{{ 'Rp ' . number_format($availableBalance, 0, ',', '.') }}</strong>
                    </p>
                </div>

                <div class="md:col-span-2">
                    <p class="text-xs text-base-content/60 mb-2">
                        Data rekening tujuan: <strong>{{ Auth::user()->tenant->bank_name }} - {{ Auth::user()->tenant->account_number }} a.n. {{ Auth::user()->tenant->account_name }}</strong>
                    </p>
                    @if(session('success'))
                        <div class="mb-3 p-3 bg-success/10 border border-success/20 text-success text-sm font-semibold rounded-xl">
                            {{ session('success') }}
                        </div>
                    @endif
                </div>

                <div class="md:col-span-3 pt-4 border-t border-base-300 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-sm font-semibold rounded-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                        @if($availableBalance < 50000) disabled @endif>
                        Ajukan Penarikan
                    </button>
                </div>
            </form>

            @if($availableBalance < 50000)
                <p class="text-center text-xs text-base-content/60 mt-3">Saldo tersedia tidak mencukupi untuk penarikan minimal.</p>
            @endif
        </div>
    @else
        <div class="bg-warning/10 border border-warning/20 rounded-2xl p-6 text-center mb-6 shadow-sm">
            <svg class="w-12 h-12 mx-auto text-warning mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <h3 class="text-lg font-bold text-base-content mb-2">Rekening Bank Belum Dikonfigurasi</h3>
            <p class="text-base-content/70 text-sm mb-4">Sebelum mengajukan penarikan, silakan isi data rekening bank Anda di halaman <a href="{{ route('tenant.settings') }}" class="text-emerald-500 hover:underline font-semibold">Pengaturan</a>.</p>
            <a href="{{ route('tenant.settings') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-sm font-semibold rounded-xl transition-all duration-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Atur Rekening Bank
            </a>
        </div>
    @endif

    {{-- Riwayat Penarikan --}}
    <div class="bg-base-100 border border-base-300 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-base-300">
            <h2 class="text-base font-bold text-base-content">Riwayat Penarikan</h2>
        </div>

        @if($withdrawals->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-base-200/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-base-content/60 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-base-content/60 uppercase tracking-wider">Nominal</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-base-content/60 uppercase tracking-wider">Rekening</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-base-content/60 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-base-content/60 uppercase tracking-wider">Proses</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-300">
                        @foreach($withdrawals as $wd)
                            <tr class="hover:bg-base-200/50 transition">
                                <td class="px-4 py-3 text-sm text-base-content">
                                    {{ \Carbon\Carbon::parse($wd->created_at)->translatedFormat('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-base-content">
                                    {{ 'Rp ' . number_format($wd->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-base-content/70">
                                    {{ $wd->bank_name }}<br>
                                    <span class="text-xs text-base-content/50">{{ $wd->account_number }} a.n. {{ $wd->account_name }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($wd->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-warning/10 text-warning">
                                            <span class="w-1.5 h-1.5 rounded-full bg-warning animate-pulse"></span>
                                            Pending
                                        </span>
                                    @elseif($wd->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-success/10 text-success">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                                            Selesai
                                            @if($wd->processed_at)
                                                <br><span class="text-[10px] opacity-70">{{ $wd->processed_at->format('d M Y H:i') }}</span>
                                            @endif
                                        </span>
                                    @elseif($wd->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-error/10 text-error">
                                            <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                                            Ditolak
                                            @if($wd->rejection_reason)
                                                <br><span class="text-[10px] opacity-70">{{ $wd->rejection_reason }}</span>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-base-content/50">
                                    @if($wd->status === 'completed' && $wd->transfer_proof_path)
                                        <a href="{{ asset('storage/' . $wd->transfer_proof_path) }}" target="_blank" class="text-emerald-500 hover:underline">Lihat Bukti</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-base-300 flex justify-center">
                {{ $withdrawals->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-base-content/20 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <p class="text-base-content/60 font-medium">Belum ada riwayat penarikan.</p>
            </div>
        @endif
    </div>
@endsection