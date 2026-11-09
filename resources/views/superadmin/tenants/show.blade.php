@extends('layouts.superadmin')

@section('title', $tenant->name)
@section('page_title', $tenant->name)

@section('content')
    @php $label = $tenant->subscriptionLabel(); @endphp

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-primary to-primary flex items-center justify-center text-white font-bold text-base sm:text-lg shadow-lg shadow-primary/30 shrink-0">
                {{ strtoupper(substr($tenant->name, 0, 2)) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold text-base-content truncate">{{ $tenant->name }}</h1>
                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ str_replace('bg-', 'bg-', $label['color']) }}">
                        {{ $label['text'] }}
                    </span>
                </div>
                <p class="text-base-content/60 text-xs sm:text-sm mt-0.5 truncate">
                    /{{ $tenant->slug }} · {{ $tenant->email }}
                    @if ($tenant->phone) · {{ $tenant->phone }} @endif
                </p>
            </div>
        </div>
        <div class="flex gap-2 shrink-0">
            <a href="{{ config('app.public_url') }}/studio/{{ $tenant->slug }}" target="_blank" rel="noopener"
               class="px-3 py-2 min-h-[40px] bg-base-200 hover:bg-base-300 text-base-content text-xs font-semibold rounded-xl transition flex items-center justify-center">
                ↗ Web
            </a>
            <a href="{{ route('superadmin.tenants.index') }}"
               class="px-3 py-2 min-h-[40px] bg-base-200 hover:bg-base-300 text-base-content/70 text-xs font-semibold rounded-xl transition flex items-center justify-center">
                ← Kembali
            </a>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        @foreach([
            ['Paket Foto', $tenant->packages_count, 'text-base-content'],
            ['Total Booking', $bookingCount, 'text-base-content'],
            ['Menunggu Bayar', $pendingCount, $pendingCount > 0 ? 'text-warning' : 'text-base-content/50'],
            ['Revenue Terverifikasi', 'Rp ' . number_format($revenue, 0, ',', '.'), 'text-success'],
        ] as $stat)
            <div class="bg-base-100 rounded-xl border border-base-300 overflow-hidden">
                <div class="stat">
                    <div class="stat-title text-xs font-semibold text-base-content/60 block mb-1">{{ $stat[0] }}</div>
                    <div class="stat-value text-base sm:text-lg font-extrabold {{ $stat[2] }}">{{ $stat[1] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- KOLOM KIRI: Info Studio + Langganan -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Info Studio -->
            <form method="POST" action="{{ route('superadmin.tenants.update', $tenant->id) }}"
                  class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-6">
                @csrf
                @method('PUT')
                <h2 class="text-lg font-bold text-base-content mb-5">Informasi Studio</h2>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Nama Studio</label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                               class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', $tenant->email) }}" required
                               class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/50 mb-1.5">WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                               class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Alamat</label>
                        <input type="text" name="address" value="{{ old('address', $tenant->address) }}"
                               class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Buka (WIB)</label>
                        <input type="time" name="operational_start" value="{{ old('operational_start', substr($tenant->operational_start, 0, 5)) }}" required
                               class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Tutup (WIB)</label>
                        <input type="time" name="operational_end" value="{{ old('operational_end', substr($tenant->operational_end, 0, 5)) }}" required
                               class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Kebijakan Booking</label>
                        <textarea name="booking_policy" rows="3"
                                  class="w-full px-3.5 py-2.5 bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">{{ old('booking_policy', $tenant->booking_policy) }}</textarea>
                    </div>
                </div>

                <div class="mt-5 pt-5 border-t border-base-300 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 min-h-[44px] bg-primary hover:bg-primary/90 text-white text-xs sm:text-sm font-semibold rounded-xl transition shadow-md shadow-primary/20">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            <!-- Langganan -->
            <form method="POST" action="{{ route('superadmin.tenants.subscription', $tenant->id) }}"
                  class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-6">
                @csrf
                @method('PUT')
                <h2 class="text-lg font-bold text-base-content mb-1">Langganan</h2>
                <p class="text-xs text-base-content/50 mb-5">Atur paket, status, dan masa trial studio.</p>

                @if ($tenant->isPartner())
                    <div class="mb-5 p-4 rounded-xl bg-violet-500/10 border border-violet-500/20 text-accent text-sm">
                        <strong>Akun Partner</strong> — akses Pro gratis selamanya, tidak pernah ditagih, dan otomatis lolos gate langganan.
                    </div>
                @endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Paket</label>
                        <select name="subscription_plan" class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
                            <option value="free" @selected($tenant->subscription_plan === 'free')>Free</option>
                            <option value="pro" @selected($tenant->subscription_plan === 'pro')>Pro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Status</label>
                        <select name="subscription_status" class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
                            <option value="active" @selected($tenant->subscription_status === 'active')>Aktif</option>
                            <option value="past_due" @selected($tenant->subscription_status === 'past_due')>Tagihan Terlambat</option>
                            <option value="canceled" @selected($tenant->subscription_status === 'canceled')>Nonaktif</option>
                        </select>
                    </div>
                    @if (! $tenant->isPartner())
                        <div>
                            <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Trial (hari dari sekarang)</label>
                            <input type="number" name="trial_days" min="0" max="365" value="0"
                                   class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                            <p class="text-[11px] text-base-content/60 mt-1">
                                @if ($tenant->trial_ends_at)
                                    Trial berakhir: {{ \Carbon\Carbon::parse($tenant->trial_ends_at)->format('d/m/Y') }}
                                @else
                                    Tidak ada trial aktif.
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Tagihan Berikutnya</label>
                            <input type="date" name="next_payment_at"
                                   value="{{ $tenant->next_payment_at ? \Carbon\Carbon::parse($tenant->next_payment_at)->format('Y-m-d') : '' }}"
                                   class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                        </div>
                    @endif
                </div>

                <div class="mt-5 pt-5 border-t border-base-300 flex flex-wrap justify-end gap-2">
                    @if (! $tenant->isPartner())
                        <button type="submit" formaction="{{ route('superadmin.tenants.extend-trial', $tenant->id) }}"
                                formmethod="POST" onclick="this.form.removeAttribute('method');"
                                class="btn btn-sm btn-ghost border-0 text-warning hover:text-amber-300 hover:bg-base-200">
                            Perpanjang Trial 14 Hari
                        </button>
                    @endif
                    <button type="submit" class="px-5 py-2.5 min-h-[44px] bg-primary hover:bg-primary/90 text-white text-xs sm:text-sm font-semibold rounded-xl transition shadow-md shadow-primary/20">
                        Simpan Langganan
                    </button>
                </div>
            </form>
        </div>

        <!-- KOLOM KANAN: Tim + Invoice + Danger -->
        <div class="space-y-6">
            <!-- Tim Studio -->
            <div class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-6">
                <h2 class="text-sm font-bold text-base-content uppercase tracking-wider mb-4">Tim Studio</h2>
                <div class="space-y-3">
                    @forelse ($tenant->users as $user)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-base-300 flex items-center justify-center font-bold text-primary text-xs shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="block text-sm font-semibold text-base-content truncate">{{ $user->name }}</span>
                                <span class="block text-xs text-base-content/60 truncate">{{ $user->email }}</span>
                            </div>
                            <span class="ml-auto shrink-0 px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $user->role === 'tenant_admin' ? 'bg-sky-500/10 text-primary' : 'bg-base-300 text-base-content/50' }}">
                                {{ $user->role === 'tenant_admin' ? 'Admin' : $user->role }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-base-content/60">Belum ada user.</p>
                    @endforelse
                </div>
            </div>

            <!-- Invoice -->
            <div class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-6">
                <h2 class="text-sm font-bold text-base-content uppercase tracking-wider mb-4">Invoice Terakhir</h2>
                @if ($tenant->invoices->isEmpty())
                    <p class="text-sm text-base-content/60">Belum ada invoice langganan.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($tenant->invoices as $invoice)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-xl border border-base-300">
                                <div class="min-w-0">
                                    <span class="block text-xs font-mono font-bold text-base-content/70">{{ $invoice->invoice_number }}</span>
                                    <span class="block text-[11px] text-base-content/60">
                                        {{ \Carbon\Carbon::parse($invoice->period_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($invoice->period_end)->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="block text-xs font-bold text-base-content">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $invoice->status === 'paid' ? 'bg-emerald-500/10 text-success' : 'bg-amber-500/10 text-warning' }}">
                                        {{ $invoice->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Danger Zone -->
            <div class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-6">
                <h2 class="text-sm font-bold text-rose-400 uppercase tracking-wider mb-2">Zona Berbahaya</h2>
                <p class="text-xs text-base-content/60 mb-4">
                    Nonaktifkan studio ini. Data booking tetap tersimpan dan bisa dipulihkan.
                </p>
                <form method="POST" action="{{ route('superadmin.tenants.destroy', $tenant->id) }}"
                      onsubmit="return confirm('Yakin ingin menonaktifkan studio {{ $tenant->name }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full btn btn-sm btn-error border-0 shadow-none">
                        Nonaktifkan Studio
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
