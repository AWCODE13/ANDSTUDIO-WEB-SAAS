@extends('layouts.tenant')

@section('title', 'Manajemen Jadwal Studio')
@section('page_title', 'Kalender & Jadwal')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl md:text-2xl font-bold text-base-content">Manajemen Jadwal Sesi</h1>
        <p class="text-base-content/60 text-xs sm:text-sm mt-1">Generate otomatis slot kosong agar klien bisa memilih waktu sesi foto.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-error/10 border border-error/20 text-error text-sm font-semibold rounded-2xl">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Mobile: Collapsible Auto-Generate Card (Agar user di HP langsung melihat kalender tanpa terhalang) -->
    <div class="lg:hidden mb-6">
        <details class="group bg-base-100 rounded-2xl border border-base-300 shadow-sm overflow-hidden" {{ $slots->isEmpty() ? 'open' : '' }}>
            <summary class="flex items-center justify-between p-4 sm:p-5 cursor-pointer font-bold text-sm text-base-content select-none">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-base-content">Auto-Generate Slot</span>
                        <span class="block text-[11px] font-normal text-base-content/65">Buat jadwal baru otomatis</span>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-base-200 text-base-content/80 group-open:hidden">+ Buat Slot</span>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-base-200 text-base-content/80 hidden group-open:inline">Tutup</span>
            </summary>
            <div class="p-4 sm:p-5 pt-2 border-t border-base-300/60">
                @include('tenant.booking_slots.partials.generator_form')
            </div>
        </details>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Desktop: Sticky Generator Sidebar -->
        <div class="hidden lg:block lg:col-span-1">
            <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm p-6 sticky top-24">
                <div class="border-b border-base-300 pb-3 mb-4">
                    <h3 class="text-base font-bold text-base-content">Auto-Generate Slot</h3>
                    <p class="text-xs text-base-content/65 mt-0.5">Buat slot kosong untuk reservasi klien</p>
                </div>
                @include('tenant.booking_slots.partials.generator_form')
            </div>
        </div>

        <!-- Kolom Kanan: Filter & Daftar Jadwal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Filter Bar -->
            <div class="bg-base-100 p-4 sm:p-5 rounded-2xl border border-base-300 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div>
                    <h2 class="text-base font-bold text-base-content">Daftar Jadwal Sesi</h2>
                    <p class="text-xs text-base-content/65 mt-0.5">Filter berdasarkan paket atau bulan</p>
                </div>
                <form method="GET" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <select name="package_filter" class="w-full sm:w-auto px-3 py-2 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none" onchange="this.form.submit()">
                        <option value="">-- Semua Paket --</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}" {{ isset($selectedPackage) && $selectedPackage == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                        @endforeach
                    </select>
                    
                    <input type="month" name="month" value="{{ $selectedMonth ?? date('Y-m') }}" class="w-full sm:w-auto px-3 py-2 min-h-[40px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none" onchange="this.form.submit()">
                </form>
            </div>

            <!-- Hari per Hari -->
            @forelse($slots as $date => $dailySlots)
                <div class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-5 shadow-sm">
                    <!-- Header Tanggal -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-base-300 pb-3 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-base-content">
                                📅 {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                            </span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-base-200 text-base-content/80">
                                {{ $dailySlots->count() }} slot
                            </span>
                        </div>
                        
                        @if(!empty($selectedPackage))
                            <form action="{{ route('tenant.booking-slots.destroy-date', ['date' => $date, 'packageId' => $selectedPackage]) }}" method="POST" onsubmit="return confirm('Yakin hapus semua jadwal kosong untuk paket ini pada tanggal ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-error hover:bg-error/10 border border-error/20 px-2.5 py-1 rounded-lg font-semibold transition self-start sm:self-auto">
                                    🗑️ Hapus Jadwal Kosong
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Grid Slot Jadwal -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-3">
                        @foreach($dailySlots as $slot)
                            <div class="border {{ $slot->status === 'available' ? 'border-base-300 bg-base-200/40' : ($slot->status === 'reserved' ? 'border-warning/30 bg-warning/5' : 'border-error/30 bg-error/5') }} rounded-xl p-2.5 sm:p-3 relative group transition">
                                
                                <!-- Nama Paket -->
                                <span class="block text-[10px] text-primary font-bold truncate text-center mb-1" title="{{ $slot->package->name ?? 'Umum' }}">
                                    {{ $slot->package->name ?? 'Umum' }}
                                </span>

                                <!-- Jam Slot -->
                                <span class="block text-sm font-extrabold text-base-content text-center tracking-tight">
                                    {{ substr($slot->start_time, 0, 5) }}
                                </span>
                                
                                <!-- Dropdown Status -->
                                <form action="{{ route('tenant.booking-slots.update-status', $slot->id) }}" method="POST" class="mt-2">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="text-[10px] font-bold uppercase tracking-wider rounded-lg border border-base-300 px-1 py-1 outline-none cursor-pointer w-full text-center {{ $slot->status === 'available' ? 'text-success bg-success/10' : ($slot->status === 'reserved' ? 'text-warning bg-warning/10' : 'text-error bg-error/10') }}">
                                        <option value="available" {{ $slot->status === 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="reserved" {{ $slot->status === 'reserved' ? 'selected' : '' }}>Reserved</option>
                                        <option value="blocked" {{ $slot->status === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                    </select>
                                </form>

                                <!-- Tombol Hapus Satuan (Visible on Mobile & Hover Desktop) -->
                                @if($slot->status === 'available')
                                    <form action="{{ route('tenant.booking-slots.destroy', $slot->id) }}" method="POST" class="absolute top-1 right-1 opacity-70 lg:opacity-0 lg:group-hover:opacity-100 transition">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-5 h-5 bg-error/20 hover:bg-error text-error hover:text-white rounded-full flex items-center justify-center text-xs font-bold" title="Hapus slot ini" aria-label="Hapus slot">×</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-base-100 p-12 text-center rounded-2xl border border-base-300">
                    <p class="text-base-content/65 font-medium">Belum ada jadwal yang di-generate pada filter ini.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
