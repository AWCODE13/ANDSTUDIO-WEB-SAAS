<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Studio - ANDstudio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base-200 flex items-center justify-center min-h-screen p-4 text-base-content">
    <div class="max-w-lg w-full bg-base-100 rounded-2xl shadow-sm border border-base-300 p-8">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center mx-auto mb-3">
                <span class="text-white text-sm font-bold">AS</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Daftar Studio Anda</h1>
            <p class="text-xs text-base-content/60 mt-1">Mulai kelola reservasi foto studio secara online</p>
        </div>

        <div class="mb-5 p-3 rounded-xl bg-success/10 border border-success/20 text-success text-xs flex items-start gap-2">
            <span class="font-bold">Trial 14 hari gratis.</span>
            <span>Tidak perlu kartu kredit. Anda bisaupgrade ke plan Pro kapan saja.</span>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-error/10 border border-error/20 text-error text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg bg-success/10 border border-success/20 text-success text-xs">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-base-content/80 mb-1">Nama Studio</label>
                <input type="text" name="studio_name" value="{{ old('studio_name') }}" required placeholder="Contoh: Kaviagraduation" class="w-full px-3 py-2 border border-base-300 rounded-xl text-sm focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-base-content/80 mb-1">Nama Anda</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama lengkap" class="w-full px-3 py-2 border border-base-300 rounded-xl text-sm focus:outline-none focus:border-primary">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-base-content/80 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="studio@email.com" class="w-full px-3 py-2 border border-base-300 rounded-xl text-sm focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/80 mb-1">Nomor WhatsApp</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 border border-base-300 rounded-xl text-sm focus:outline-none focus:border-primary">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-base-content/80 mb-1">Kata Sandi</label>
                    <input type="password" name="password" required minlength="8" placeholder="Min. 8 karakter" class="w-full px-3 py-2 border border-base-300 rounded-xl text-sm focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-base-content/80 mb-1">Ulangi Kata Sandi</label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi kata sandi" class="w-full px-3 py-2 border border-base-300 rounded-xl text-sm focus:outline-none focus:border-primary">
                </div>
            </div>

            <label class="flex items-start gap-2 text-xs text-base-content/70">
                <input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded border-base-300 text-primary focus:ring-primary">
                <span>Saya menyetujui Syarat & Ketentuan ANDstudio.</span>
            </label>

            <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-2.5 rounded-xl text-sm transition shadow-sm shadow-primary/30">
                Buat Akun Studio Saya
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-base-300 text-center text-xs text-base-content/60">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Masuk di sini</a>
        </div>
    </div>
</body>
</html>
