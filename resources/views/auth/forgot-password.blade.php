<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - ANDstudio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base-200 flex items-center justify-center min-h-screen p-4 text-base-content">
    <div class="max-w-md w-full bg-base-100 rounded-2xl shadow-sm border border-base-300 p-8">
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center mx-auto mb-3">
                <span class="text-white text-sm font-bold">AS</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Lupa Kata Sandi</h1>
            <p class="text-xs text-base-content/60 mt-1">Masukkan email studio Anda untuk menerima link reset</p>
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

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-base-content/80 mb-1">Email Studio</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-3 py-2 border border-base-300 rounded-xl text-sm focus:outline-none focus:border-primary">
            </div>

            <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-2.5 rounded-xl text-sm transition shadow-sm shadow-primary/30">
                Kirim Link Reset
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-base-300 text-center text-xs text-base-content/60">
            <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">← Kembali ke halaman masuk</a>
        </div>
    </div>
</body>
</html>
