@extends('layouts.tenant')

@section('title', 'Pembayaran Langganan')
@section('page_title', 'Pembayaran Langganan')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
 <!-- Header -->
 <div class="mb-8">
 <a href="{{ route('tenant.billing.index') }}" class="inline-flex items-center gap-1 text-xs text-base-content/60 hover:text-primary mb-4">
 ← Kembali ke Plan & Tagihan
 </a>
 <h1 class="text-2xl font-bold text-base-content">Pembayaran Langganan</h1>
 <p class="text-base-content/60 text-sm mt-1">Selesaikan pembayaran untuk mengaktifkan Plan Pro.</p>
 </div>

 @if(session('success'))
 <div class="mb-5 p-4 rounded-xl bg-success/10 border border-success/20 text-success text-sm font-semibold">✓ {{ session('success') }}</div>
 @endif
 @if(session('error'))
 <div class="mb-5 p-4 rounded-xl bg-error/10 border border-error/20 text-error text-sm font-semibold">{{ session('error') }}</div>
 @endif

 <!-- Invoice Detail Card -->
 <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-6 mb-6">
 <div class="text-center mb-6">
 <div class="w-16 h-16 bg-primary/20 text-primary rounded-full flex items-center justify-center mx-auto mb-4">
 <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
 <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
 </svg>
 </div>
 <h2 class="text-lg font-bold text-base-content mb-1">Detail Tagihan</h2>
 <p class="text-sm text-base-content/60">{{ $invoice->invoice_number }}</p>
 </div>

 <div class="space-y-3 text-sm mb-6 border-t border-b border-base-300 py-4">
 <div class="flex justify-between">
 <span class="text-base-content/60">Periode</span>
 <span class="font-semibold text-base-content">
 {{ \Carbon\Carbon::parse($invoice->period_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($invoice->period_end)->format('d/m/Y') }}
 </span>
 </div>
 <div class="flex justify-between">
 <span class="text-base-content/60">Plan</span>
 <span class="font-semibold text-base-content capitalize">{{ $invoice->plan }}</span>
 </div>
 <div class="flex justify-between pt-2">
 <span class="text-base-content/60 font-semibold">Total</span>
 <span class="font-black text-success text-lg">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
 </div>
 </div>

 <!-- Pay Button -->
 <button id="pay-button"
 class="w-full bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] font-bold py-3.5 px-4 rounded-xl transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)] flex items-center justify-center gap-2"
 data-snap-token="{{ $snapToken }}"
 >
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
 <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
 </svg>
 Bayar Sekarang via Midtrans
 </button>

 <a href="{{ route('tenant.billing.index') }}" class="block w-full mt-3 bg-base-200 hover:bg-base-300 text-base-content font-semibold py-3.5 px-4 rounded-xl transition text-center">
 Kembali ke Plan & Tagihan
 </a>
 </div>

 <!-- Info bantuan -->
 <div class="p-4 rounded-xl bg-base-200/50 border border-base-300 text-sm text-base-content/70">
 <p class="font-semibold mb-2">Informasi Pembayaran</p>
 <ul class="space-y-1 text-xs">
 <li class="flex items-start gap-2">• Pembayaran diproses melalui <strong>Midtrans</strong> (QRIS, VA, e-Wallet, dll)</li>
 <li class="flex items-start gap-2">• Slot jadwal diamankan otomatis begitu pembayaran berhasil</li>
 <li class="flex items-start gap-2">• Invoice ini berlaku selama 24 jam dari dibuat</li>
 <li class="flex items-start gap-2">• Jika pop-up dibuka, pastikan tidak diblokir browser</li>
 </ul>
 </div>
</div>

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
 document.getElementById('pay-button').onclick = function () {
 const snapToken = this.getAttribute('data-snap-token');
 snap.pay(snapToken, {
 onSuccess: function (result) {
 alert("Pembayaran berhasil! Plan Pro akan aktif dalam beberapa detik.");
 window.location.href = "{{ route('tenant.billing.index') }}";
 },
 onPending: function (result) {
 alert("Menunggu pembayaran Anda diselesaikan.");
 window.location.href = "{{ route('tenant.billing.index') }}";
 },
 onError: function (result) {
 alert("Pembayaran gagal. Silakan coba lagi.");
 },
 onClose: function () {
 console.log('Pop-up ditutup tanpa menyelesaikan pembayaran');
 }
 });
 };
</script>
@endpush
@endsection