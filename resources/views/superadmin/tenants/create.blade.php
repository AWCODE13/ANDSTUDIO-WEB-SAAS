@extends('layouts.superadmin')

@section('title', 'Tambah Studio')
@section('page_title', 'Tambah Studio')

@section('content')
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('superadmin.tenants.index') }}"
           class="w-10 h-10 rounded-xl bg-base-200 hover:bg-base-300 text-base-content flex items-center justify-center font-bold text-sm transition shrink-0">
            ←
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-base-content">Tambah Studio Baru</h1>
            <p class="text-base-content/60 text-xs sm:text-sm mt-0.5">Buat tenant dan akun admin studio sekaligus.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('superadmin.tenants.store') }}"
          class="max-w-3xl space-y-6">
        @csrf

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Data Studio -->
        <div class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-6">
            <h2 class="text-lg font-bold text-base-content mb-5">Data Studio</h2>

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Nama Studio <span class="text-warning">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Kaviagraduation"
                           class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                    <p class="text-[11px] text-base-content/60 mt-1">Slug URL akan dibuat otomatis dari nama ini.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Email Studio <span class="text-warning">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="halo@studio.com"
                           class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"
                           class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Alamat</label>
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="Alamat lengkap studio"
                           class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                </div>
            </div>
        </div>

        <!-- Akun Admin -->
        <div class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-6">
            <h2 class="text-lg font-bold text-base-content mb-1">Akun Admin Studio</h2>
            <p class="text-xs text-base-content/50 mb-5">Akun ini yang akan dipakai untuk masuk ke dashboard studio.</p>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Nama Admin <span class="text-warning">*</span></label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required placeholder="Nama lengkap"
                           class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Email Admin <span class="text-warning">*</span></label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="admin@studio.com"
                           class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Kata Sandi <span class="text-warning">*</span></label>
                    <input type="text" name="admin_password" value="{{ old('admin_password') }}" required minlength="8" placeholder="Min. 8 karakter"
                           class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                    <p class="text-[11px] text-base-content/60 mt-1">Sampaikan password ini ke pemilik studio secara aman.</p>
                </div>
            </div>
        </div>

        <!-- Langganan -->
        <div class="bg-base-100 rounded-2xl border border-base-300 p-4 sm:p-6">
            <h2 class="text-lg font-bold text-base-content mb-5">Langganan</h2>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Paket <span class="text-warning">*</span></label>
                    <select name="subscription_plan" class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
                        <option value="free" @selected(old('subscription_plan') === 'free')>Free</option>
                        <option value="pro" @selected(old('subscription_plan') === 'pro')>Pro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Status <span class="text-warning">*</span></label>
                    <select name="subscription_status" class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content focus:border-primary outline-none">
                        <option value="active" @selected(old('subscription_status', 'active') === 'active')>Aktif</option>
                        <option value="past_due" @selected(old('subscription_status') === 'past_due')>Tagihan Terlambat</option>
                        <option value="canceled" @selected(old('subscription_status') === 'canceled')>Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/50 mb-1.5">Trial (hari)</label>
                    <input type="number" name="trial_days" value="{{ old('trial_days', 14) }}" min="0" max="365"
                           class="w-full px-3.5 py-2.5 min-h-[44px] bg-base-100 border border-base-300 rounded-xl text-xs sm:text-sm font-medium text-base-content placeholder-base-content/50 focus:border-primary outline-none">
                    <p class="text-[11px] text-base-content/60 mt-1">Isi 0 jika tidak ada trial.</p>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2.5 text-sm text-base-content/70 cursor-pointer pb-2.5">
                        <input type="checkbox" name="is_partner" value="1" @checked(old('is_partner'))
                               class="input input-checkbox input-sm rounded border-base-300 text-sky-500 focus:ring-sky-500">
                        <span>Jadikan akun <strong>Partner</strong> (gratis selamanya)</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
            <a href="{{ route('superadmin.tenants.index') }}"
               class="px-4 py-2.5 min-h-[44px] bg-base-200 hover:bg-base-300 text-base-content/70 text-xs sm:text-sm font-semibold rounded-xl transition flex items-center justify-center">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 min-h-[44px] bg-primary hover:bg-primary/90 text-white text-xs sm:text-sm font-semibold rounded-xl transition shadow-md shadow-primary/20 flex items-center justify-center">
                Buat Studio
            </button>
        </div>
    </form>
@endsection