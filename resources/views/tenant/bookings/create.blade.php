@extends('layouts.tenant')

@section('title', 'Tambah Reservasi Manual')
@section('page_title', 'Tambah Reservasi Manual')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl md:text-2xl font-bold text-base-content">Tambah Reservasi Manual</h1>
        <p class="text-base-content/60 text-sm mt-1">Catat booking offline / walk-in / WhatsApp tunai ke sistem agar jadwal terbaca dan keuangan terhitung.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-error/10 border border-error/20 text-error text-sm font-semibold rounded-xl">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.bookings.store') }}" class="bg-base-100 border border-base-300 rounded-2xl p-5 sm:p-6 shadow-sm space-y-6" id="manualBookingForm">
        @csrf

        {{-- Data Klien --}}
        <div class="space-y-5 border-b border-base-300 pb-5">
            <h2 class="text-base font-bold text-base-content flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Data Klien
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="client_name">Nama Klien <span class="text-error">*</span></label>
                    <input type="text" id="client_name" name="client_name" value="{{ old('client_name') }}"
                        placeholder="Nama lengkap klien"
                        class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none"
                        required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="client_whatsapp">No. WhatsApp <span class="text-error">*</span></label>
                    <input type="text" id="client_whatsapp" name="client_whatsapp" value="{{ old('client_whatsapp') }}"
                        placeholder="08xxxxxxxxxx"
                        class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none"
                        required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="client_email">Email (Opsional)</label>
                    <input type="email" id="client_email" name="client_email" value="{{ old('client_email') }}"
                        placeholder="email@domain.com (kosongkan jika tidak ada)"
                        class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none">
                    <p class="text-xs text-base-content/60 mt-1">Jika dikosongkan, sistem akan menggenerate email otomatis untuk keperluan database.</p>
                </div>
            </div>
        </div>

        {{-- Pemilihan Layanan --}}
        <div class="space-y-5 border-b border-base-300 pb-5">
            <h2 class="text-base font-bold text-base-content flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Pemilihan Layanan
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="package_id">Paket Foto <span class="text-error">*</span></label>
                    <select id="package_id" name="package_id" required
                        class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none">
                        <option value="">-- Pilih Paket --</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}"
                                data-price="{{ $pkg->price }}"
                                data-duration="{{ $pkg->duration_minutes }}"
                                data-dp-min="{{ $pkg->down_payment_min }}"
                                @selected(old('package_id') == $pkg->id)>
                                {{ $pkg->name }} (Rp {{ number_format($pkg->price, 0, ',', '.') }}, {{ $pkg->duration_minutes }} menit)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="booking_date">Tanggal Reservasi <span class="text-error">*</span></label>
                    <input type="date" id="booking_date" name="booking_date" value="{{ old('booking_date', now()->addDay()->format('Y-m-d')) }}"
                        min="{{ now()->format('Y-m-d') }}"
                        class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none"
                        required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="booking_slot_id">Slot Waktu Tersedia</label>
                    <div class="relative">
                        <select id="booking_slot_id" name="booking_slot_id"
                            class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none appearance-none pr-10">
                            <option value="">-- Pilih Tanggal & Paket Dahulu --</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <label class="block text-xs font-semibold text-base-content/70 mt-2 mb-1.5" for="manual_time">Atau Jam Manual</label>
                    <input type="time" id="manual_time" name="manual_time" value="{{ old('manual_time') }}"
                        class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none">
                    <p class="text-xs text-base-content/60 mt-1">Pilih slot, atau isi jam manual jika jadwal belum digenerate. Slot akan otomatis diblokir.</p>
                </div>
            </div>
        </div>

        {{-- Detail Pembayaran --}}
        <div class="space-y-5">
            <h2 class="text-base font-bold text-base-content flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Detail Pembayaran
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Status Pembayaran <span class="text-error">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="payment_status" value="paid" {{ old('payment_status', 'paid') === 'paid' ? 'checked' : '' }}
                                class="radio radio-primary">
                            <span class="text-sm font-medium text-base-content">Lunas Tunai (100%)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="payment_status" value="dp_paid" {{ old('payment_status') === 'dp_paid' ? 'checked' : '' }}
                                class="radio radio-primary">
                            <span class="text-sm font-medium text-base-content">DP Tunai (Min. 30%)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="payment_method">Metode Pembayaran</label>
                    <select id="payment_method" name="payment_method"
                        class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="manual_transfer">Transfer Manual (sudah diterima)</option>
                    </select>
                </div>

                <div id="dp_amount_field" class="md:col-span-2 hidden">
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="dp_amount">Jumlah DP (Rp) <span class="text-error">*</span></label>
                    <input type="number" id="dp_amount" name="dp_amount" value="{{ old('dp_amount') }}"
                        placeholder="Masukkan nominal DP"
                        class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none"
                        min="0" step="1000">
                    <p class="text-xs text-base-content/60 mt-1">Minimal 30% dari harga paket. Kosongkan untuk pakai default.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-base-300 bg-base-200/50 rounded-xl p-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <span class="block text-xs text-base-content/60">Total Harga</span>
                        <span id="total_price_display" class="block text-lg font-bold text-emerald-500">Rp 0</span>
                    </div>
                    <div>
                        <span class="block text-xs text-base-content/60">DP Minimum</span>
                        <span id="dp_min_display" class="block text-lg font-bold text-warning">Rp 0</span>
                    </div>
                    <div>
                        <span class="block text-xs text-base-content/60">Sudah Dibayar</span>
                        <span id="paid_amount_display" class="block text-lg font-bold text-success">Rp 0</span>
                    </div>
                    <div>
                        <span class="block text-xs text-base-content/60">Sisa</span>
                        <span id="remaining_display" class="block text-lg font-bold text-error">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-base-300">
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="notes">Catatan (Opsional)</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Catatan tambahan..."
                        class="w-full px-3 py-2 bg-white border border-gray-300 dark:bg-white/5 dark:border-white/20 dark:text-white text-gray-900 focus:ring-emerald-500 rounded-lg outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-base-300">
                    <a href="{{ route('tenant.bookings.index') }}"
                        class="px-5 py-2.5 bg-base-200 hover:bg-base-300 text-base-content text-sm font-semibold rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-sm font-semibold rounded-xl transition-all duration-300">
                        Simpan Reservasi
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('package_id');
    const dateInput = document.getElementById('booking_date');
    const slotSelect = document.getElementById('booking_slot_id');
    const manualTimeInput = document.getElementById('manual_time');
    const paymentStatusRadios = document.querySelectorAll('input[name="payment_status"]');
    const paymentMethodSelect = document.getElementById('payment_method');
    const dpAmountField = document.getElementById('dp_amount_field');
    const dpAmountInput = document.getElementById('dp_amount');

    const totalPriceDisplay = document.getElementById('total_price_display');
    const dpMinDisplay = document.getElementById('dp_min_display');
    const paidAmountDisplay = document.getElementById('paid_amount_display');
    const remainingDisplay = document.getElementById('remaining_display');

    let currentPackage = null;

    function formatRupiah(num) {
        return 'Rp ' + num.toLocaleString('id-ID');
    }

    function updatePaymentSummary() {
        if (!currentPackage) {
            totalPriceDisplay.textContent = 'Rp 0';
            dpMinDisplay.textContent = 'Rp 0';
            paidAmountDisplay.textContent = 'Rp 0';
            remainingDisplay.textContent = 'Rp 0';
            return;
        }

        const total = parseFloat(currentPackage.dataset.price);
        const dpMin = parseFloat(currentPackage.dataset.dpMin) || Math.round(total * 0.30 * 100) / 100;
        const isFull = document.querySelector('input[name="payment_status"]:checked')?.value === 'paid';

        totalPriceDisplay.textContent = formatRupiah(total);
        dpMinDisplay.textContent = formatRupiah(dpMin);

        let paid = 0;
        if (isFull) {
            paid = total;
            dpAmountField.classList.add('hidden');
            dpAmountInput.removeAttribute('required');
        } else {
            const inputDp = parseFloat(dpAmountInput.value);
            paid = (inputDp > 0) ? inputDp : dpMin;
            if (paid >= total) paid = total;
            dpAmountField.classList.remove('hidden');
            dpAmountInput.setAttribute('required', 'required');
        }

        paidAmountDisplay.textContent = formatRupiah(paid);
        remainingDisplay.textContent = formatRupiah(Math.max(0, total - paid));
    }

    // Saat paket berubah
    packageSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        currentPackage = opt.value ? opt : null;
        slotSelect.innerHTML = '<option value="">-- Pilih Tanggal Dahulu --</option>';
        updatePaymentSummary();
        fetchSlots();
    });

    // Saat tanggal berubah
    dateInput.addEventListener('change', fetchSlots);

    // Ambil slot tersedia via AJAX
    function fetchSlots() {
        const pkgId = packageSelect.value;
        const date = dateInput.value;
        if (!pkgId || !date) {
            slotSelect.innerHTML = '<option value="">-- Pilih Tanggal & Paket Dahulu --</option>';
            return;
        }
        slotSelect.innerHTML = '<option value="">Memuat slot...</option>';

        fetch('{{ route("tenant.bookings.available-slots") }}?package_id=' + pkgId + '&date=' + date)
            .then(res => res.json())
            .then(data => {
                slotSelect.innerHTML = '<option value="">-- Tidak pakai slot (isi jam manual) --</option>';
                if (!data.length) {
                    slotSelect.innerHTML += '<option value="" disabled>Tidak ada slot tersedia</option>';
                    return;
                }
                data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.label;
                    slotSelect.appendChild(opt);
                });
            })
            .catch(() => {
                slotSelect.innerHTML = '<option value="">Gagal memuat — isi jam manual</option>';
            });
    }

    // Pilih slot -> kosongkan jam manual, dan sebaliknya
    slotSelect.addEventListener('change', function() {
        if (this.value) manualTimeInput.value = '';
    });
    manualTimeInput.addEventListener('input', function() {
        if (this.value) slotSelect.value = '';
    });

    // Radio payment status
    paymentStatusRadios.forEach(r => r.addEventListener('change', updatePaymentSummary));

    // DP amount input
    dpAmountInput.addEventListener('input', updatePaymentSummary);

    // Inisialisasi
    if (packageSelect.value) {
        currentPackage = packageSelect.options[packageSelect.selectedIndex];
    }
    updatePaymentSummary();
});
</script>
@endpush