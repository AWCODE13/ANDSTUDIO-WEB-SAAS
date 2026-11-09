@extends('layouts.tenant')

@section('title', 'Manajemen Reservasi')
@section('page_title', 'Daftar Reservasi')

@section('content')
 <div class="mb-6 flex justify-between items-center">
   <div>
     <h1 class="text-xl md:text-2xl font-bold text-base-content">Daftar Reservasi Klien</h1>
     <p class="text-base-content/60 text-sm mt-1">Kelola pesanan masuk, terima pelunasan, dan kirim hasil foto.</p>
   </div>
   <a href="{{ route('tenant.bookings.create') }}"
     class="px-5 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-sm font-semibold rounded-xl transition-all duration-300 flex items-center gap-2">
     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
     Tambah Reservasi Manual
   </a>
 </div>

 @if(session('success'))
 <div class="mb-6 p-4 bg-success/10 text-success text-sm font-semibold rounded-xl border border-success/20">
 ✓ {{ session('success') }}
 </div>
 @endif

 <!-- Desktop Table -->
 <div class="hidden lg:block bg-base-100 rounded-2xl border border-base-300 shadow-sm overflow-hidden">
 <div class="overflow-x-auto">
 <table class="w-full text-left text-sm whitespace-nowrap">
 <thead class="bg-base-200 text-base-content/60 font-semibold border-b border-base-300">
 <tr>
 <th class="px-6 py-4">Kode & Klien</th>
 <th class="px-6 py-4">Paket & Jadwal</th>
 <th class="px-6 py-4">Status Tagihan</th>
 <th class="px-6 py-4 text-center">Aksi / Link Hasil</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-100">
 @forelse ($bookings as $booking)
 @php
 $status = strtolower($booking->booking_status ?? $booking->status ?? '');
 
 $actualTotalPrice = $booking->total_price > 0 ? $booking->total_price : ($booking->package->price ?? 0);
 
 $totalPaid = $booking->payments->where('status', 'verified')->sum('amount');
 $remainingBalance = $actualTotalPrice - $totalPaid;
 $isFullyPaid = $remainingBalance <= 0;
 @endphp
 
 <tr class="hover:bg-base-200 transition">
 <!-- Kolom 1: Klien -->
 <td class="px-6 py-4">
 <span class="block font-bold text-base-content">{{ $booking->customer_name }}</span>
 <span class="block text-xs text-base-content/60 mt-0.5">{{ $booking->customer_phone }}</span>
 <span class="inline-block mt-2 px-2 py-0.5 bg-base-200 text-base-content/70 text-[10px] font-bold rounded">#{{ $booking->booking_code ?? $booking->id }}</span>
 </td>
 
 <!-- Kolom 2: Paket & Waktu -->
 <td class="px-6 py-4">
 <span class="block font-bold text-primary">{{ $booking->package->name ?? 'Paket Dihapus' }}</span>
 <div class="flex items-center gap-1.5 text-xs text-base-content/70 mt-1 font-medium">
 <span>📅 {{ \Carbon\Carbon::parse($booking->bookingSlot->date ?? $booking->booking_date ?? now())->format('d/m/Y') }}</span>
 <span>•</span>
 <span>⏰ {{ substr($booking->bookingSlot->start_time ?? $booking->start_time ?? '00:00', 0, 5) }}</span>
 </div>
 </td>
 
 <!-- Kolom 3: Status Tagihan & Info Detail Pembayaran -->
 <td class="px-6 py-4">
 <div class="text-xs mb-2 border-b border-base-300 pb-2">
 <span class="block text-base-content/60">Total: Rp {{ number_format($actualTotalPrice, 0, ',', '.') }}</span>
 <span class="block text-success font-bold mt-0.5">Sudah Verifikasi: Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
 
 @if(!$isFullyPaid && $status !== 'pending')
 <span class="inline-block mt-1 px-1.5 py-0.5 bg-error/20 text-error font-bold rounded">
 Kurang: Rp {{ number_format($remainingBalance, 0, ',', '.') }}
 </span>
 @endif
 </div>
 
 <!-- Detail Riwayat Pembayaran -->
 <div class="space-y-1.5 mt-2">
 <span class="block text-[9px] font-bold text-base-content/50 uppercase tracking-wider">Riwayat Pembayaran:</span>
 @foreach($booking->payments as $payment)
 <div class="flex items-center justify-between text-[10px] bg-base-100 p-1.5 rounded border border-base-300">
 <div class="flex flex-col">
 <span class="font-bold text-base-content/80 uppercase">{{ $payment->payment_type }} ({{ $payment->payment_method }})</span>
 <span class="text-base-content/60">Rp {{ number_format($payment->amount, 0, ',', '.') }} • 
 @if($payment->status === 'verified')
 <span class="text-success font-bold">Verified</span>
 @else
 <span class="text-warning font-bold">Pending</span>
 @endif
 </span>
 </div>
 @if($payment->proof_attachment_path)
 <a href="{{ asset('storage/' . $payment->proof_attachment_path) }}" target="_blank" class="px-2 py-1 bg-primary/10 text-primary font-bold rounded hover:bg-primary/20 transition">
 Lihat Bukti
 </a>
 @else
 <span class="text-base-content/70 italic">Via Cash</span>
 @endif
 </div>
 @endforeach
 </div>
 </td>
 
 <!-- Kolom 4: Aksi -->
 <td class="px-6 py-4 text-center align-top">
 @if($status === 'pending')
 <!-- Tombol Validasi Bukti Transfer DP -->
 <form action="{{ route('tenant.bookings.verify-payment', $booking->id) }}" method="POST">
 @csrf @method('PATCH')
 <button type="submit" class="w-full lg:w-auto bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] px-3 py-2 lg:py-1.5 rounded text-xs lg:text-[11px] font-bold transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)]">
 Validasi
 </button>
 </form>
 <span class="block text-[9px] text-base-content/50 italic mt-1 lg:mt-0.5">Menunggu validasi</span>

 @elseif($status === 'confirmed' && !$isFullyPaid)
 <!-- Modal Pelunasan (DaisyUI dialog) -->
 <button type="button" onclick="document.getElementById('modal_pay_{{ $booking->id }}').showModal()" class="btn btn-xs sm:btn-sm btn-success text-white font-bold shadow-sm whitespace-nowrap">
 Pelunasan
 </button>
 <dialog id="modal_pay_{{ $booking->id }}" class="modal text-left">
 <div class="modal-box bg-base-100 border border-base-300 max-w-sm">
 <h3 class="font-bold text-base text-base-content">Konfirmasi Pelunasan</h3>
 <p class="py-2 text-xs text-base-content/70">
 Terima pelunasan untuk reservasi <strong>#{{ $booking->booking_code ?? $booking->id }}</strong> ({{ $booking->customer_name }}).
 </p>
 <div class="my-3 p-3 bg-base-200 rounded-xl flex justify-between items-center text-xs">
 <span class="text-base-content/70">Sisa Tagihan:</span>
 <span class="font-extrabold text-success text-sm">Rp {{ number_format($remainingBalance, 0, ',', '.') }}</span>
 </div>
 <form action="{{ route('tenant.bookings.pay-cash', $booking->id) }}" method="POST">
 @csrf
 <div class="mb-4">
 <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Metode Diterima</label>
 <select name="payment_method" required class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm focus:border-primary outline-none">
 <option value="cash">Tunai (Cash)</option>
 <option value="transfer_manual">Transfer Manual</option>
 </select>
 </div>
 <div class="modal-action mt-6 flex gap-2">
 <button type="button" onclick="document.getElementById('modal_pay_{{ $booking->id }}').close()" class="btn btn-sm btn-ghost flex-1">Batal</button>
 <button type="submit" class="btn btn-sm btn-success text-white flex-1">Konfirmasi</button>
 </div>
 </form>
 </div>
 <form method="dialog" class="modal-backdrop">
 <button>close</button>
 </form>
 </dialog>
 <!-- PERBAIKAN: Form GDrive muncul jika Lunas ATAU jika status Selesai tapi linknya kosong -->
 @elseif(($status === 'confirmed' && $isFullyPaid) || ($status === 'completed' && empty($booking->archive_link)))
 
 <span class="inline-block mb-2 px-2 py-0.5 bg-success/20 text-success text-[10px] font-bold rounded">STATUS: LUNAS</span>
 
 <form action="{{ route('tenant.bookings.archive', $booking->id) }}" method="POST" class="flex flex-col lg:flex-row lg:items-end gap-2 lg:gap-1 mt-1">
 @csrf @method('PATCH')
 <!-- Value link tetap ada meski WA gagal -->
 <input type="url" name="archive_link" value="{{ $booking->archive_link }}" placeholder="Link GDrive..." class="w-full lg:flex-1 px-2 py-2 lg:py-1.5 border {{ session('wa_error_id') == $booking->id ? 'border-error bg-error/10 text-error' : 'border-base-300' }} rounded text-xs focus:border-primary outline-none" required>
 <button type="submit" class="w-full lg:w-auto bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] px-3 py-2 lg:py-1.5 rounded text-xs lg:text-[11px] font-bold transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)] lg:whitespace-nowrap">
 Kirim
 </button>
 </form>
 
 <!-- Pesan Error Merah Jika Gagal -->
 @if(session('wa_error_id') == $booking->id)
 <span class="block text-[9px] text-error font-bold mt-1 text-center bg-error/10 py-1 rounded">{{ session('wa_error_msg') }}</span>
 @endif

 <!-- PERBAIKAN: Tombol Lihat Link HANYA muncul jika linknya benar-benar tidak kosong -->
 @elseif($status === 'completed' && !empty($booking->archive_link))
 <div class="text-center">
 <span class="block px-2 py-1 bg-success/20 text-success text-[10px] font-bold rounded mb-1">FILE SUDAH DIKIRIM ✓</span>
 <a href="{{ $booking->archive_link }}" target="_blank" class="text-xs font-bold text-primary hover:text-primary underline">Lihat Link ↗</a>
 </div>
 @endif
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-6 py-12 text-center text-base-content/60">Belum ada reservasi masuk.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 <!-- Mobile Cards -->
 <div class="lg:hidden space-y-4">
 @forelse ($bookings as $booking)
 @php
 $status = strtolower($booking->booking_status ?? $booking->status ?? '');
 $actualTotalPrice = $booking->total_price > 0 ? $booking->total_price : ($booking->package->price ?? 0);
 $totalPaid = $booking->payments->where('status', 'verified')->sum('amount');
 $remainingBalance = $actualTotalPrice - $totalPaid;
 $isFullyPaid = $remainingBalance <= 0;
 @endphp
 
 <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-4 space-y-4">
 <!-- Header -->
 <div>
 <span class="block font-bold text-base-content">{{ $booking->customer_name }}</span>
 <span class="block text-xs text-base-content/60 mt-0.5">{{ $booking->customer_phone }}</span>
 <span class="inline-block mt-2 px-2 py-0.5 bg-base-200 text-base-content/70 text-[10px] font-bold rounded">#{{ $booking->booking_code ?? $booking->id }}</span>
 </div>
 
 <!-- Paket & Jadwal -->
 <div>
 <span class="block font-bold text-primary text-sm">{{ $booking->package->name ?? 'Paket Dihapus' }}</span>
 <div class="flex items-center gap-1.5 text-xs text-base-content/70 mt-1 font-medium">
 <span>📅 {{ \Carbon\Carbon::parse($booking->bookingSlot->date ?? $booking->booking_date ?? now())->format('d/m/Y') }}</span>
 <span>•</span>
 <span>⏰ {{ substr($booking->bookingSlot->start_time ?? $booking->start_time ?? '00:00', 0, 5) }}</span>
 </div>
 </div>
 
 <!-- Status Pembayaran -->
 <div class="text-xs border-t border-base-300 pt-3">
 <div class="flex justify-between mb-2">
 <span class="text-base-content/60">Total:</span>
 <span class="font-bold">Rp {{ number_format($actualTotalPrice, 0, ',', '.') }}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-base-content/60">Sudah Verifikasi:</span>
 <span class="text-success font-bold">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
 </div>
 @if(!$isFullyPaid && $status !== 'pending')
 <div class="flex justify-between mt-2">
 <span class="text-base-content/60">Kurang:</span>
 <span class="text-error font-bold">Rp {{ number_format($remainingBalance, 0, ',', '.') }}</span>
 </div>
 @endif
 </div>
 
 <!-- Aksi -->
 <div class="space-y-2">
 @if($status === 'pending')
 <form action="{{ route('tenant.bookings.verify-payment', $booking->id) }}" method="POST">
 @csrf @method('PATCH')
 <button type="submit" class="w-full bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] px-3 py-2 rounded text-xs font-bold transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)]">
 Validasi Pembayaran
 </button>
 </form>
 <span class="block text-[10px] text-base-content/50 text-center">Menunggu validasi DP admin</span>
 
 @elseif($status === 'confirmed' && !$isFullyPaid)
 <!-- Modal Pelunasan Mobile -->
 <button type="button" onclick="document.getElementById('modal_pay_m_{{ $booking->id }}').showModal()" class="w-full btn btn-sm btn-success text-white font-bold shadow-sm">
 Pelunasan
 </button>
 <dialog id="modal_pay_m_{{ $booking->id }}" class="modal text-left">
 <div class="modal-box bg-base-100 border border-base-300 max-w-sm">
 <h3 class="font-bold text-base text-base-content">Konfirmasi Pelunasan</h3>
 <p class="py-2 text-xs text-base-content/70">
 Reservasi <strong>#{{ $booking->booking_code ?? $booking->id }}</strong> ({{ $booking->customer_name }}).
 </p>
 <div class="my-3 p-3 bg-base-200 rounded-xl flex justify-between items-center text-xs">
 <span class="text-base-content/70">Sisa Tagihan:</span>
 <span class="font-extrabold text-success text-sm">Rp {{ number_format($remainingBalance, 0, ',', '.') }}</span>
 </div>
 <form action="{{ route('tenant.bookings.pay-cash', $booking->id) }}" method="POST">
 @csrf
 <div class="mb-4">
 <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Metode Diterima</label>
 <select name="payment_method" required class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm focus:border-primary outline-none">
 <option value="cash">Tunai (Cash)</option>
 <option value="transfer_manual">Transfer Manual</option>
 </select>
 </div>
 <div class="modal-action mt-6 flex gap-2">
 <button type="button" onclick="document.getElementById('modal_pay_m_{{ $booking->id }}').close()" class="btn btn-sm btn-ghost flex-1">Batal</button>
 <button type="submit" class="btn btn-sm btn-success text-white flex-1">Konfirmasi</button>
 </div>
 </form>
 </div>
 <form method="dialog" class="modal-backdrop">
 <button>close</button>
 </form>
 </dialog>
 @elseif(($status === 'confirmed' && $isFullyPaid) || ($status === 'completed' && empty($booking->archive_link)))
 <span class="block px-2 py-1 bg-success/20 text-success text-[10px] font-bold rounded text-center mb-2">STATUS: LUNAS</span>
 <form action="{{ route('tenant.bookings.archive', $booking->id) }}" method="POST" class="space-y-2">
 @csrf @method('PATCH')
 <input type="url" name="archive_link" value="{{ $booking->archive_link }}" placeholder="Link GDrive..." class="w-full px-3 py-2 border {{ session('wa_error_id') == $booking->id ? 'border-error bg-error/10 text-error' : 'border-base-300' }} rounded text-sm focus:border-primary outline-none" required>
 <button type="submit" class="w-full bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] px-3 py-2 rounded text-xs font-bold transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)]">
 Kirim via WA
 </button>
 </form>
 @if(session('wa_error_id') == $booking->id)
 <span class="block text-[10px] text-error font-bold text-center">{{ session('wa_error_msg') }}</span>
 @endif
 
 @elseif($status === 'completed' && !empty($booking->archive_link))
 <div class="text-center space-y-2">
 <span class="block px-2 py-1 bg-success/20 text-success text-[10px] font-bold rounded">FILE SUDAH DIKIRIM ✓</span>
 <a href="{{ $booking->archive_link }}" target="_blank" class="inline-block text-xs font-bold text-primary hover:text-primary underline">Lihat Link ↗</a>
 </div>
 @endif
 </div>
 
 <!-- Riwayat Pembayaran (Expandable jika banyak) -->
 @if($booking->payments->isNotEmpty())
 <details class="text-xs">
 <summary class="font-bold text-base-content/60 cursor-pointer">Riwayat Pembayaran</summary>
 <div class="mt-2 space-y-2">
 @foreach($booking->payments as $payment)
 <div class="bg-base-200 p-2 rounded border border-base-300">
 <div class="flex justify-between">
 <span class="font-bold text-base-content/80">{{ $payment->payment_type }} ({{ $payment->payment_method }})</span>
 <span class="text-base-content/70">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
 </div>
 <div class="flex justify-between mt-1">
 <span class="text-base-content/60">
 @if($payment->status === 'verified')
 <span class="text-success font-bold">Verified</span>
 @else
 <span class="text-warning font-bold">Pending</span>
 @endif
 </span>
 @if($payment->proof_attachment_path)
 <a href="{{ asset('storage/' . $payment->proof_attachment_path) }}" target="_blank" class="text-primary font-bold text-[10px]">Lihat Bukti</a>
 @endif
 </div>
 </div>
 @endforeach
 </div>
 </details>
 @endif
 </div>
 @empty
 <div class="bg-base-100 p-8 text-center rounded-2xl border border-base-300">
 <p class="text-base-content/60 font-medium">Belum ada reservasi masuk.</p>
 </div>
 @endforelse
 </div>
@endsection