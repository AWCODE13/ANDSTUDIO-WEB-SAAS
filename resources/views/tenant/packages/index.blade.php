@extends('layouts.tenant')

@section('title', 'Katalog Paket Foto')
@section('page_title', 'Paket Foto')

@section('content')
 <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
 <div>
 <h1 class="text-xl md:text-2xl font-bold text-base-content">Katalog Paket Foto</h1>
 <p class="text-base-content/60 text-sm mt-1">Kelola layanan dan harga paket studio Anda.</p>
 </div>
 <a href="{{ route('tenant.packages.create') }}" class="px-5 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] font-semibold rounded-xl transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-center sm:text-left shrink-0">
 + Tambah Paket Baru
 </a>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 @forelse($packages as $package)
 <div class="bg-base-100 rounded-2xl border border-base-300 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.05)] relative overflow-hidden flex flex-col">
 @if(!$package->is_active)
 <span class="absolute top-4 right-4 bg-error/20 text-error text-[10px] font-bold px-2 py-1 rounded-md uppercase z-10">Nonaktif</span>
 @endif

 <!-- Foto Paket -->
 <div class="relative h-40 w-full overflow-hidden rounded-t-xl bg-base-200/50 border-b border-base-300/50">
 @php
 $firstPhoto = $package->photos->first();
 @endphp
 @if($firstPhoto)
 <img src="{{ $firstPhoto->url }}" alt="{{ $package->name }}" class="w-full h-full object-cover" loading="lazy">
 @else
 <div class="w-full h-full flex items-center justify-center bg-base-200/30">
 <svg class="w-12 h-12 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
 </svg>
 <div class="text-xs text-base-content/50 mt-2">Tidak ada foto</div>
 </div>
 @endif
 </div>

 <div class="p-5 flex-1 flex flex-col">
 <div class="text-xs font-bold text-primary bg-primary/10 px-2.5 py-1 rounded-md inline-block uppercase tracking-wider mb-3">{{ $package->category }}</div>
 <h3 class="text-lg font-bold text-base-content mb-1">{{ $package->name }}</h3>
 <p class="text-sm text-base-content/60 line-clamp-2 flex-1 mb-4">{{ $package->description }}</p>

 <div class="mt-auto pt-4 border-t border-base-300">
 <div class="flex justify-between items-end mb-4">
 <div>
 <span class="text-xs text-base-content/50 block">Harga</span>
 <span class="text-lg font-extrabold text-base-content">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
 </div>
 <div class="text-right">
 <span class="text-xs text-base-content/50 block">Min. DP</span>
 <span class="text-sm font-bold text-success">Rp {{ number_format($package->down_payment_min, 0, ',', '.') }}</span>
 </div>
 </div>

 <div class="flex gap-2">
 <a href="{{ route('tenant.packages.edit', $package->id) }}" class="flex-1 text-center bg-base-200 hover:bg-base-300 text-base-content/80 text-xs font-bold py-2.5 rounded-lg transition">Edit Paket</a>
 <form action="{{ route('tenant.packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus paket ini?');">
 @csrf @method('DELETE')
 <button type="submit" class="bg-error/10 hover:bg-error/20 text-error px-3 py-2.5 rounded-lg transition" title="Hapus" aria-label="Hapus Paket">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
 </button>
 </form>
 </div>
 </div>
 </div>
 </div>
 @empty
 <div class="col-span-full bg-base-100 p-12 text-center rounded-2xl border border-base-300">
 <p class="text-base-content/60 font-medium">Belum ada paket foto yang dibuat.</p>
 </div>
 @endforelse
 </div>
 @endsection
