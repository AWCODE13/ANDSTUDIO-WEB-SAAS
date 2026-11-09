<form action="{{ route('tenant.booking-slots.generate') }}" method="POST" class="space-y-4">
 @csrf
 <div>
 <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Pilih Layanan / Paket</label>
 <select name="package_id" class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-sm font-medium text-base-content focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none" required>
 <option value="">-- Pilih Paket Foto --</option>
  <option value="all" {{ old('package_id') == 'all' ? 'selected' : '' }}>-- Semua Paket (Generate Sekaligus) --</option>
 @foreach($packages as $pkg)
 <option value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
 @endforeach
 </select>
 </div>

 <!-- Rentang Tanggal -->
 <div class="space-y-1.5">
 <label class="block text-xs font-semibold text-base-content/70">Rentang Tanggal</label>
 <div class="grid grid-cols-2 gap-2 sm:gap-3">
 <div>
 <span class="block text-[10px] text-base-content/50 uppercase tracking-wider mb-1 font-semibold">Mulai</span>
 <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
 class="w-full px-2.5 sm:px-3 py-2 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none" required>
 </div>
 <div>
 <span class="block text-[10px] text-base-content/50 uppercase tracking-wider mb-1 font-semibold">Selesai</span>
 <input type="date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}"
 class="w-full px-2.5 sm:px-3 py-2 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none" required>
 </div>
 </div>
 </div>

 <!-- Rentang Jam Sesi -->
 <div class="space-y-1.5">
 <label class="block text-xs font-semibold text-base-content/70">Jam Operasional Sesi</label>
 <div class="grid grid-cols-2 gap-2 sm:gap-3">
 <div>
 <span class="block text-[10px] text-base-content/50 uppercase tracking-wider mb-1 font-semibold">Buka</span>
 <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}"
 class="w-full px-2.5 sm:px-3 py-2 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none" required>
 </div>
 <div>
 <span class="block text-[10px] text-base-content/50 uppercase tracking-wider mb-1 font-semibold">Tutup</span>
 <input type="time" name="end_time" value="{{ old('end_time', '21:00') }}"
 class="w-full px-2.5 sm:px-3 py-2 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none" required>
 </div>
 </div>
 </div>

 <div>
 <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Durasi per Sesi</label>
 <select name="interval" class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-sm font-medium text-base-content focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none">
 <option value="30" {{ old('interval') == '30' ? 'selected' : '' }}>30 Menit</option>
 <option value="45" {{ old('interval') == '45' ? 'selected' : '' }}>45 Menit</option>
 <option value="60" {{ old('interval', '60') == '60' ? 'selected' : '' }}>60 Menit (1 Jam)</option>
 <option value="90" {{ old('interval') == '90' ? 'selected' : '' }}>90 Menit (1.5 Jam)</option>
 <option value="120" {{ old('interval') == '120' ? 'selected' : '' }}>120 Menit (2 Jam)</option>
 </select>
 </div>

 <button type="submit" class="w-full py-3 min-h-[44px] bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] font-bold rounded-xl transition-all duration-300 shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-sm flex items-center justify-center gap-2">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
 Generate Jadwal Otomatis
 </button>
</form>
