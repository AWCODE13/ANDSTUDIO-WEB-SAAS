@extends('layouts.tenant')

@section('title', 'Laporan Keuangan')
@section('page_title', 'Keuangan & Pengeluaran')

@section('content')
 <div class="mb-6">
 <h1 class="text-2xl font-bold text-base-content">Catatan Pengeluaran</h1>
 <p class="text-base-content/60 text-sm mt-1">Kelola arus kas keluar untuk operasional studio Anda.</p>
 </div>

 @if(session('success'))
 <div class="mb-6 p-4 bg-success/10 text-success text-sm font-semibold rounded-xl border border-success/20">
 ✓ {{ session('success') }}
 </div>
 @endif

 <!-- Desktop Layout -->
 <div class="hidden lg:grid lg:grid-cols-3 gap-6">
 <!-- Kolom Form Tambah Pengeluaran -->
 <div class="lg:col-span-1">
 <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-6 sticky top-24">
 <h3 class="text-base font-bold text-base-content mb-4 border-b border-base-300 pb-3">Catat Pengeluaran Baru</h3>
 <form action="{{ route('tenant.expenses.store') }}" method="POST" class="space-y-4">
 @csrf
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Tanggal</label>
 <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Kategori</label>
 <select name="category" class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none" required>
 <option value="operational">Operasional (Listrik, Internet)</option>
 <option value="equipment">Peralatan / Properti Foto</option>
 <option value="salary">Gaji Staff / Fotografer</option>
 <option value="marketing">Marketing / Iklan</option>
 <option value="others">Lainnya</option>
 </select>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Judul Pengeluaran</label>
 <input type="text" name="title" placeholder="Contoh: Beli lampu flash baru..." class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Nominal (Rp)</label>
 <input type="number" name="amount" placeholder="500000" class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Catatan Tambahan (Opsional)</label>
 <textarea name="notes" rows="2" class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none"></textarea>
 </div>
 <button type="submit" class="w-full py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] font-bold rounded-xl transition-all duration-300 mt-2 text-sm shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)]">
 Simpan Pengeluaran
 </button>
 </form>
 </div>
 </div>

 <!-- Kolom Daftar Pengeluaran -->
 <div class="lg:col-span-2 space-y-6">
 <div class="bg-base-100 p-4 rounded-2xl border border-base-300 shadow-sm flex items-center justify-between">
 <div>
 <span class="text-sm font-bold text-base-content block">Total Pengeluaran Bulan Ini</span>
 <span class="text-xl font-extrabold text-error">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
 </div>
 <form method="GET" class="flex gap-2">
 <input type="month" name="month" value="{{ $selectedMonth }}" class="px-3 py-1.5 border border-base-300 rounded-lg text-sm outline-none" onchange="this.form.submit()">
 </form>
 </div>

 <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm overflow-hidden">
 <table class="w-full text-left text-sm">
 <thead class="bg-base-200 text-base-content/60 font-semibold border-b border-base-300">
 <tr>
 <th class="px-6 py-4">Tanggal</th>
 <th class="px-6 py-4">Kategori & Judul</th>
 <th class="px-6 py-4">Nominal</th>
 <th class="px-6 py-4">Aksi</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-100">
 @forelse ($expenses as $expense)
 <tr class="hover:bg-base-200">
 <td class="px-6 py-4 font-medium">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
 <td class="px-6 py-4">
 <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-base-200 text-base-content/70 mb-1">{{ $expense->category }}</span>
 <p class="text-base-content font-medium">{{ $expense->title }}</p>
 @if($expense->notes)
 <p class="text-[11px] text-base-content/60 mt-1">{{ $expense->notes }}</p>
 @endif
 </td>
 <td class="px-6 py-4 font-bold text-error">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
 <td class="px-6 py-4">
 <form action="{{ route('tenant.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
 @csrf @method('DELETE')
 <button type="submit" class="text-error hover:text-error text-xs font-bold">Hapus</button>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-6 py-12 text-center text-base-content/60">Belum ada pengeluaran di bulan ini.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <!-- Mobile Layout -->
 <div class="lg:hidden space-y-4">
 <!-- Form Mobile -->
 <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-4">
 <h3 class="text-base font-bold text-base-content mb-4 border-b border-base-300 pb-3">Catat Pengeluaran Baru</h3>
 <form action="{{ route('tenant.expenses.store') }}" method="POST" class="space-y-3">
 @csrf
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Tanggal</label>
 <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Kategori</label>
 <select name="category" class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none" required>
 <option value="operational">Operasional</option>
 <option value="equipment">Peralatan</option>
 <option value="salary">Gaji Staff</option>
 <option value="marketing">Marketing</option>
 <option value="others">Lainnya</option>
 </select>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Judul Pengeluaran</label>
 <input type="text" name="title" placeholder="Contoh: Beli lampu..." class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Nominal (Rp)</label>
 <input type="number" name="amount" placeholder="500000" class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none" required>
 </div>
 <div>
 <label class="block text-xs font-bold text-base-content/80 mb-1">Catatan (Opsional)</label>
 <textarea name="notes" rows="2" class="w-full px-3 py-2 bg-base-200 border border-base-300 rounded-lg text-sm text-base-content focus:border-primary outline-none"></textarea>
 </div>
 <button type="submit" class="w-full py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] font-bold rounded-xl transition-all duration-300 text-sm shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)]">
 Simpan Pengeluaran
 </button>
 </form>
 </div>

 <!-- Ringkasan -->
 <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-4 flex items-center justify-between">
 <div>
 <span class="text-sm font-bold text-base-content block">Total Bulan Ini</span>
 <span class="text-xl font-extrabold text-error">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
 </div>
 <form method="GET" class="flex gap-2">
 <input type="month" name="month" value="{{ $selectedMonth }}" class="px-3 py-1.5 border border-base-300 rounded-lg text-sm outline-none" onchange="this.form.submit()">
 </form>
 </div>

 <!-- List Pengeluaran -->
 <div class="space-y-3">
 @forelse ($expenses as $expense)
 <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-4">
 <div class="flex justify-between items-start">
 <div>
 <div class="text-sm font-medium">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</div>
 <div class="mt-2">
 <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-base-200 text-base-content/70 mb-1">{{ $expense->category }}</span>
 <p class="text-base-content font-medium text-sm">{{ $expense->title }}</p>
 @if($expense->notes)
 <p class="text-xs text-base-content/60 mt-1">{{ $expense->notes }}</p>
 @endif
 </div>
 </div>
 <div class="text-right">
 <div class="font-bold text-error">Rp {{ number_format($expense->amount, 0, ',', '.') }}</div>
 <form action="{{ route('tenant.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
 @csrf @method('DELETE')
 <button type="submit" class="text-error hover:text-error text-xs font-bold mt-2">Hapus</button>
 </form>
 </div>
 </div>
 </div>
 @empty
 <div class="bg-base-100 p-8 text-center rounded-2xl border border-base-300">
 <p class="text-base-content/60 font-medium">Belum ada pengeluaran di bulan ini.</p>
 </div>
 @endforelse
 </div>
 </div>
@endsection