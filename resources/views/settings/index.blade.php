@extends(request()->routeIs('superadmin.*') ? 'layouts.superadmin' : 'layouts.tenant')

@section('title', __('settings.title'))
@section('page_title', __('settings.page_title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">{{ __('settings.title') }}</h1>
        <p class="text-base-content/70 text-sm mt-1">{{ __('settings.page_title') }}</p>
    </div>

    <div class="bg-base-100 rounded-xl border border-base-300 p-6 max-w-3xl">
        <form method="POST" action="{{ request()->routeIs('superadmin.*') ? route('superadmin.settings.update') : route('tenant.settings.update') }}">
            @csrf
            @method('PUT')

            <!-- Mode Tampilan -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-base-content mb-3">{{ __('settings.theme') }}</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="theme" value="dark" @checked($user->theme === 'dark')
                               class="radio radio-primary">
                        <span class="text-base-content font-medium">{{ __('settings.theme_dark') }}</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="theme" value="light" @checked($user->theme === 'light')
                               class="radio radio-primary">
                        <span class="text-base-content font-medium">{{ __('settings.theme_light') }}</span>
                    </label>
                </div>
            </div>

            <!-- Bahasa -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-base-content mb-3">{{ __('settings.locale') }}</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="locale" value="id" @checked($user->locale === 'id')
                               class="radio radio-primary">
                        <span class="text-base-content font-medium">{{ __('settings.locale_id') }}</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="locale" value="en" @checked($user->locale === 'en')
                               class="radio radio-primary">
                        <span class="text-base-content font-medium">{{ __('settings.locale_en') }}</span>
                    </label>
                </div>
            </div>

            <!-- Negara -->
            <div class="mb-8">
                <label class="block text-sm font-bold text-base-content mb-2" for="country">{{ __('settings.country') }}</label>
                <select id="country" name="country" class="select select-bordered w-full max-w-xs bg-base-200 border-base-300 text-base-content focus:border-primary">
                    @foreach ($countries as $code => $name)
                        <option value="{{ $code }}" @selected($user->country === $code)>{{ $name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-base-content/60 mt-2">{{ __('settings.country_note') }}</p>
            </div>

            <div class="pt-5 border-t border-base-300 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-sm font-semibold rounded-xl transition-all duration-300">
                    {{ __('settings.save') }}
                </button>
            </div>
        </form>
    </div>

    @if(Auth::user()->tenant)
        <!-- Rekening Bank Pencairan Saldo -->
        <div class="bg-base-100 rounded-xl border border-base-300 p-6 max-w-3xl mt-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Rekening Bank Pencairan Dana
                </h2>
                <p class="text-xs text-base-content/60 mt-1">Data rekening ini akan digunakan secara otomatis saat Anda mengajukan penarikan saldo studio.</p>
            </div>

            <form method="POST" action="{{ route('tenant.settings.bank') }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="bank_name">Nama Bank / E-Wallet</label>
                        <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', Auth::user()->tenant->bank_name) }}"
                            placeholder="BCA, Mandiri, BRI, BNI..."
                            class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm font-medium text-base-content focus:border-emerald-500 outline-none" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="account_number">Nomor Rekening</label>
                        <input type="text" id="account_number" name="account_number" value="{{ old('account_number', Auth::user()->tenant->account_number) }}"
                            placeholder="Contoh: 1234567890"
                            class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm font-medium text-base-content focus:border-emerald-500 outline-none" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5" for="account_name">Nama Pemilik Rekening</label>
                        <input type="text" id="account_name" name="account_name" value="{{ old('account_name', Auth::user()->tenant->account_name) }}"
                            placeholder="Sesuai buku tabungan"
                            class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm font-medium text-base-content focus:border-emerald-500 outline-none" required>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-base-300">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-md border-transparent dark:bg-white/10 dark:backdrop-blur-md dark:border-white/20 dark:text-white dark:hover:bg-white/20 dark:shadow-[0_0_15px_rgba(16,185,129,0.4)] text-sm font-semibold rounded-xl transition-all duration-300">
                        Simpan Rekening Bank
                    </button>
                </div>
            </form>
        </div>
    @endif
@endsection
