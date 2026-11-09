@extends('layouts.tenant')

@section('title', 'Ulasan Pelanggan')
@section('page_title', 'Ulasan Pelanggan')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Ulasan Pelanggan</h1>
        <p class="text-base-content/60 text-sm mt-1">
            Rating dihitung dari ulasan yang tampil. Ulasan tidak bisa dihapus sendiri — ajukan hapus ke super admin.
        </p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-5">
            <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider">Rating</span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-2xl font-extrabold text-base-content">{{ number_format($stats['rating'], 1, ',', '.') }}</span>
                <span class="text-warning text-lg">★</span>
            </div>
            <p class="text-[11px] text-base-content/50 mt-1">Dari {{ $stats['visible'] }} ulasan tampil</p>
        </div>

        <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-5">
            <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider">Total Ulasan</span>
            <div class="text-2xl font-extrabold text-base-content mt-1">{{ $stats['total'] }}</div>
            <p class="text-[11px] text-base-content/50 mt-1">Sejak studio aktif</p>
        </div>

        <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-5">
            <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider">Menunggu</span>
            <div class="text-2xl font-extrabold text-warning mt-1">{{ $stats['pending'] }}</div>
            <p class="text-[11px] text-base-content/50 mt-1">Permintaan hapus diproses super admin</p>
        </div>

        <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-5">
            <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider">Disembunyikan</span>
            <div class="text-2xl font-extrabold text-base-content/50 mt-1">{{ $stats['hidden'] }}</div>
            <p class="text-[11px] text-base-content/50 mt-1">Tidak tampil publik, tidak dihitung rating</p>
        </div>
    </div>

    {{-- Daftar ulasan --}}
    <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-base-300 flex items-center justify-between">
            <h2 class="text-base font-bold text-base-content">Semua Ulasan</h2>
            <span class="text-xs font-semibold text-base-content/50">{{ $reviews->total() }} ulasan</span>
        </div>

        @if ($reviews->isEmpty())
            <div class="px-6 py-16 text-center">
                <span class="text-4xl">💬</span>
                <p class="text-base-content/60 text-sm mt-3">Belum ada ulasan dari pelanggan.</p>
                <p class="text-base-content/50 text-xs mt-1">Ulasan muncul setelah pelanggan menyelesaikan sesi foto dan memberi rating.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($reviews as $review)
                    @php
                        $pendingRequest = $review->deletionRequests->firstWhere('status', 'pending');
                        $lastRequest = $review->deletionRequests->first();
                    @endphp
                    <div class="px-6 py-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <span class="text-warning tracking-tight">
                                        @for ($i = 1; $i <= 5; $i++)
                                            {{ $i <= $review->rating ? '★' : '☆' }}
                                        @endfor
                                    </span>
                                    <span class="text-sm font-bold text-base-content">{{ $review->maskedName() }}</span>
                                    <span class="text-xs text-base-content/50">{{ $review->created_at?->translatedFormat('d M Y H:i') }}</span>
                                    @if ($review->package)
                                        <span class="text-[11px] font-semibold text-base-content/60 bg-base-200 px-2 py-0.5 rounded-full">
                                            {{ $review->package->name }}
                                        </span>
                                    @endif

                                    @if ($review->isHidden())
                                        <span class="text-[11px] font-bold text-base-content/60 bg-base-300 px-2 py-0.5 rounded-full">
                                            Disembunyikan (disetujui super admin)
                                        </span>
                                    @elseif ($pendingRequest)
                                        <span class="text-[11px] font-bold text-warning bg-warning/10 px-2 py-0.5 rounded-full">
                                            Menunggu persetujuan hapus
                                        </span>
                                    @elseif ($lastRequest && $lastRequest->status === 'rejected')
                                        <span class="text-[11px] font-bold text-error bg-error/10 px-2 py-0.5 rounded-full">
                                            Permintaan hapus ditolak
                                        </span>
                                    @endif
                                </div>

                                @if ($review->comment)
                                    <p class="text-sm text-base-content/70 leading-relaxed">“{{ $review->comment }}”</p>
                                @else
                                    <p class="text-sm text-base-content/50 italic">Tanpa komentar.</p>
                                @endif

                                @if ($review->isHidden() && $review->hidden_reason)
                                    <p class="text-[11px] text-base-content/50 mt-2">
                                        Alasan disembunyikan: {{ $review->hidden_reason }}
                                    </p>
                                @endif

                                @if ($lastRequest && $lastRequest->status === 'rejected' && $lastRequest->admin_note)
                                    <p class="text-[11px] text-error mt-2">
                                        Catatan super admin: {{ $lastRequest->admin_note }}
                                    </p>
                                @endif
                            </div>

                            <div class="shrink-0">
                                @if (! $review->isHidden() && ! $pendingRequest)
                                    <details class="relative">
                                        <summary class="cursor-pointer list-none text-xs font-bold text-error bg-error/10 hover:bg-error/20 px-3 py-2 rounded-lg transition">
                                            Ajukan Hapus
                                        </summary>
                                        <form action="{{ route('tenant.reviews.request-deletion', $review) }}" method="POST"
                                              class="mt-3 w-72 bg-base-200 border border-base-300 rounded-xl p-4 space-y-3">
                                            @csrf
                                            <p class="text-xs text-base-content/60 leading-relaxed">
                                                Ulasan tidak langsung dihapus. Permintaan diteruskan ke super admin
                                                ANDstudio untuk diverifikasi.
                                            </p>
                                            <div>
                                                <label class="block text-xs font-bold text-base-content/80 mb-1">Alasan</label>
                                                <textarea name="reason" rows="3" required
                                                          placeholder="Contoh: ulasan tidak relevan / mengandung data pribadi"
                                                          class="w-full px-3 py-2 bg-base-100 bg-base-100 border border-base-300 rounded-lg text-sm focus:border-primary outline-none"></textarea>
                                            </div>
                                            <button type="submit" class="w-full py-2 bg-error hover:bg-error text-white font-bold rounded-xl text-xs transition">
                                                Kirim Permintaan
                                            </button>
                                        </form>
                                    </details>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($reviews->hasPages())
                <div class="px-6 py-4 border-t border-base-300">
                    {{ $reviews->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
