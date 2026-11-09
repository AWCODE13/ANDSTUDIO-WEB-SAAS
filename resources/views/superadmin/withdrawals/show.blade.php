@extends('layouts.superadmin')

@section('title', 'Detail Pencairan')
@section('page_title', 'Detail Pencairan')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Pengajuan #{{ $withdrawal->id }}</h1>
            <p class="text-base-content/50 text-sm mt-1">Diajukan {{ $withdrawal->created_at->translatedFormat('d M Y H:i') }}</p>
        </div>
        <a href="{{ route('superadmin.withdrawals.index') }}" class="px-3 py-2 min-h-[40px] bg-base-200 hover:bg-base-300 text-base-content/70 text-sm font-semibold rounded-xl transition flex items-center">
            ← Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Detail --}}
        <div class="lg:col-span-2 bg-base-100 rounded-2xl border border-base-300 p-5 sm:p-6 space-y-5">
            <div>
                <span class="block text-xs font-semibold text-base-content/60">Nominal Penarikan</span>
                <span class="block text-3xl font-extrabold text-emerald-500 mt-1">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-5 border-t border-base-300">
                <div>
                    <span class="block text-xs font-semibold text-base-content/60">Studio</span>
                    <span class="block text-sm font-bold text-base-content mt-0.5">{{ $withdrawal->tenant->name ?? '—' }}</span>
                    <span class="block text-xs text-base-content/60">{{ $withdrawal->tenant->email ?? '' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-base-content/60">Status</span>
                    @php
                        $map = ['pending' => ['bg-warning/10 text-warning', 'Pending'],
                                'completed' => ['bg-success/10 text-success', 'Selesai'],
                                'rejected' => ['bg-error/10 text-error', 'Ditolak']];
                        $badge = $map[$withdrawal->status] ?? ['bg-base-300 text-base-content/50', $withdrawal->status];
                    @endphp
                    <span class="inline-block mt-1 px-2 py-1 rounded-md text-[10px] font-bold uppercase {{ $badge[0] }}">{{ $badge[1] }}</span>
                    @if($withdrawal->processed_at)
                        <span class="block text-xs text-base-content/60 mt-1">Diproses {{ $withdrawal->processed_at->translatedFormat('d M Y H:i') }}</span>
                    @endif
                </div>
                <div>
                    <span class="block text-xs font-semibold text-base-content/60">Bank</span>
                    <span class="block text-sm text-base-content mt-0.5">{{ $withdrawal->bank_name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-base-content/60">Nomor Rekening</span>
                    <span class="block text-sm text-base-content mt-0.5">{{ $withdrawal->account_number }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-base-content/60">Nama Pemilik</span>
                    <span class="block text-sm text-base-content mt-0.5">{{ $withdrawal->account_name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-base-content/60">Bukti Transfer</span>
                    @if($withdrawal->transfer_proof_path)
                        <a href="{{ asset('storage/' . $withdrawal->transfer_proof_path) }}" target="_blank" rel="noopener" class="block text-sm text-emerald-500 hover:underline mt-0.5">Lihat Bukti</a>
                    @else
                        <span class="block text-sm text-base-content/50 mt-0.5">—</span>
                    @endif
                </div>
            </div>

            @if($withdrawal->rejection_reason)
                <div class="p-4 rounded-xl bg-error/10 border border-error/20">
                    <span class="block text-xs font-bold text-error uppercase tracking-wide">Alasan Penolakan</span>
                    <p class="text-sm text-base-content mt-1">{{ $withdrawal->rejection_reason }}</p>
                </div>
            @endif
        </div>

        {{-- Aksi --}}
        <div class="space-y-6">
            @if($withdrawal->status === 'pending')
                <div class="bg-base-100 rounded-2xl border border-base-300 p-5">
                    <h2 class="text-base font-bold text-base-content mb-1">Tandai Selesai</h2>
                    <p class="text-xs text-base-content/60 mb-4">Konfirmasi bahwa dana sudah ditransfer ke rekening studio.</p>

                    <form method="POST" action="{{ route('superadmin.withdrawals.complete', $withdrawal) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="transfer_proof">Bukti Transfer (opsional)</label>
                            <input type="file" id="transfer_proof" name="transfer_proof" accept=".jpg,.jpeg,.png,.pdf"
                                   class="w-full px-3 py-2 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs font-medium text-base-content file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-base-200 file:text-base-content/70 file:text-xs file:font-semibold focus:border-emerald-500 outline-none">
                            <p class="text-[11px] text-base-content/50 mt-1">JPG, PNG, atau PDF. Maks 2MB.</p>
                        </div>
                        <button type="submit" class="w-full px-4 py-2.5 min-h-[44px] bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-sm font-semibold rounded-xl transition-all duration-300">
                            Tandai Selesai
                        </button>
                    </form>
                </div>

                <div class="bg-base-100 rounded-2xl border border-base-300 p-5">
                    <h2 class="text-base font-bold text-base-content mb-1">Tolak Pengajuan</h2>
                    <p class="text-xs text-base-content/60 mb-4">Alasan akan ditampilkan ke studio.</p>

                    <form method="POST" action="{{ route('superadmin.withdrawals.reject', $withdrawal) }}" class="space-y-3" onsubmit="return confirm('Tolak pengajuan penarikan ini?')">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="rejection_reason">Alasan Penolakan</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="3" required maxlength="500"
                                      class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm font-medium text-base-content placeholder-base-content/50 focus:border-error outline-none"
                                      placeholder="Contoh: data rekening tidak sesuai..."></textarea>
                            @error('rejection_reason')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="w-full px-4 py-2.5 min-h-[44px] bg-error hover:bg-error/90 text-white text-sm font-semibold rounded-xl transition">
                            Tolak Pengajuan
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-base-100 rounded-2xl border border-base-300 p-5 text-center">
                    <p class="text-sm text-base-content/60">Pengajuan ini sudah diproses.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
