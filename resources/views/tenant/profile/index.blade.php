@extends('layouts.tenant')

@section('title', 'Profil Studio')
@section('page_title', 'Profil Studio')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Profil Studio</h1>
        <p class="text-base-content/60 text-sm mt-1">Kelola informasi studio, jam operasional, dan akun admin Anda.</p>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Profil Studio -->
        <form method="POST" action="{{ route('tenant.profile.update') }}" enctype="multipart/form-data"
              class="bg-base-100 rounded-2xl border border-base-300 p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.05)]">
            @csrf
            @method('PUT')

            <h2 class="text-lg font-bold text-base-content mb-5">Informasi Studio</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Nama Studio</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                           class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                </div>

                {{-- Logo / Foto Studio --}}
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Logo / Foto Studio</label>

                    @if ($tenant->logo_path)
                        <div class="flex items-start gap-4 mb-3">
                            <img src="{{ $tenant->logo_url }}" alt="Logo {{ $tenant->name }}"
                                 class="w-24 h-24 rounded-2xl object-cover border border-base-300 bg-base-200 shrink-0">
                            <label class="flex items-center gap-2 text-sm text-base-content/80 mt-1 cursor-pointer">
                                <input type="checkbox" name="remove_logo" value="1"
                                       class="rounded border-base-300 text-error focus:ring-error">
                                Hapus Foto
                            </label>
                        </div>
                    @else
                        <p class="text-[11px] text-base-content/50 mb-2">Belum ada logo. Unggah JPG/PNG (maks 5MB).</p>
                    @endif

                    <input type="file" name="logo" accept="image/jpeg,image/png"
                           class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 file:mr-3 file:rounded-lg file:border-0 file:bg-neutral file:text-white file:px-3 file:py-1.5 file:text-xs file:font-semibold hover:file:bg-neutral/90 focus:outline-none focus:border-primary">
                    <p class="text-[11px] text-base-content/50 mt-1">Upload baru akan menggantikan logo lama (file lama otomatis dihapus).</p>
                    @error('logo')
                        <p class="text-[11px] text-error mt-1">{{ $message }}</p>
                    @enderror
                    @error('remove_logo')
                        <p class="text-[11px] text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Instagram</label>
                    <input type="text" name="instagram_url" value="{{ old('instagram_url', $tenant->instagram_url) }}"
                           placeholder="@namastudio atau instagram.com/namastudio" class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    <p class="text-[11px] text-base-content/50 mt-1">Ditampilkan sebagai link di halaman profil studio. Rating diambil otomatis dari ulasan pelanggan.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Email Studio</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}" required
                           class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    <p class="text-[11px] text-base-content/50 mt-1">Email ini dipakai untuk invoice & notifikasi.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5">WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                           class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Alamat</label>
                    <textarea name="address" rows="2"
                              class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">{{ old('address', $tenant->address) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Buka (WIB)</label>
                        <input type="time" name="operational_start" value="{{ old('operational_start', substr($tenant->operational_start, 0, 5)) }}" required
                               class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Tutup (WIB)</label>
                        <input type="time" name="operational_end" value="{{ old('operational_end', substr($tenant->operational_end, 0, 5)) }}" required
                               class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Kebijakan Booking</label>
                    <textarea name="booking_policy" rows="3"
                              class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">{{ old('booking_policy', $tenant->booking_policy) }}</textarea>
                    <p class="text-[11px] text-base-content/50 mt-1">Ditampilkan di halaman katalog studio.</p>
                </div>
            </div>

            <div class="mt-5 pt-5 border-t border-base-300 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-neutral text-white text-sm font-semibold rounded-xl hover:bg-neutral/90 transition">
                    Simpan Profil
                </button>
            </div>
        </form>

        <div class="space-y-6">
            <!-- Akun Admin -->
            <form method="POST" action="{{ route('tenant.profile.account') }}"
                  class="bg-base-100 rounded-2xl border border-base-300 p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.05)]">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-bold text-base-content mb-1">Akun Admin</h2>
                <p class="text-xs text-base-content/60 mb-5">Data ini dipakai untuk login ke dashboard.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Nama</label>
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required
                               class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required
                               class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5">WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}"
                               class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    </div>
                </div>

                <div class="mt-5 pt-5 border-t border-base-300 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-neutral text-white text-sm font-semibold rounded-xl hover:bg-neutral/90 transition">
                        Simpan Akun
                    </button>
                </div>
            </form>

            <!-- Ganti Password -->
            <form method="POST" action="{{ route('tenant.profile.password') }}"
                  class="bg-base-100 rounded-2xl border border-base-300 p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.05)]">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-bold text-base-content mb-1">Ganti Kata Sandi</h2>
                <p class="text-xs text-base-content/60 mb-5">Gunakan kombinasi huruf, angka, dan simbol yang kuat.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required
                               class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Kata Sandi Baru</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-base-content/70 mb-1.5">Ulangi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" required minlength="8"
                               class="w-full px-3 py-2 bg-base-100 border border-base-300 rounded-xl text-sm text-base-content placeholder-base-content/40 focus:outline-none focus:border-primary">
                    </div>
                </div>

                <div class="mt-5 pt-5 border-t border-base-300 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-neutral text-white text-sm font-semibold rounded-xl hover:bg-neutral/90 transition">
                        Ubah Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
