<!DOCTYPE html>
<html lang="id" data-theme="{{ ($theme ?? 'dark') === 'light' ? 'studio-light' : 'studio-dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk - ANDstudio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-base-200 min-h-screen text-base-content antialiased flex flex-col justify-between p-4 sm:p-6">

    <!-- Header Atas: Toggle Tema -->
    <header class="w-full max-w-md mx-auto flex justify-end pt-2 pb-4">
        <button type="button" onclick="toggleTheme()" class="w-10 h-10 rounded-xl bg-base-100 border border-base-300 text-base-content/70 hover:text-base-content flex items-center justify-center transition shadow-sm" aria-label="Ganti Tema">
            <svg id="theme-icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <svg id="theme-icon-moon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
        </button>
    </header>

    <!-- Konten Utama -->
    <main class="w-full max-w-md mx-auto my-auto">
        <!-- Logo & Judul -->
        <div class="text-center mb-6">
            <img src="{{ asset('images/studio.png') }}" alt="ANDstudio" class="h-12 w-auto object-contain mx-auto mb-3">
            <h1 class="text-2xl font-extrabold text-base-content tracking-tight">Selamat Datang</h1>
            <p class="text-xs sm:text-sm text-base-content/60 mt-1">Masuk ke Portal Manajemen Studio Foto</p>
        </div>

        <!-- Kartu Form -->
        <div class="bg-base-100 border border-base-300 rounded-3xl p-6 sm:p-8 shadow-xl">
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-error/10 border border-error/20 text-error text-xs font-semibold flex items-start gap-2.5">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 p-3.5 rounded-xl bg-success/10 border border-success/20 text-success text-xs font-semibold flex items-start gap-2.5">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="loginForm" class="space-y-4">
                @csrf

                <!-- Input Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-base-content/70 mb-1.5">Email Studio</label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           autocomplete="email"
                           placeholder="studio@contoh.com"
                           class="w-full h-12 px-4 rounded-xl bg-base-200/50 border border-base-300 text-base-content placeholder-base-content/30 text-sm font-medium focus:outline-none focus:border-primary focus:bg-base-100 transition">
                </div>

                <!-- Input Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-base-content/70">Kata Sandi</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-primary hover:underline">Lupa kata sandi?</a>
                    </div>
                    <div class="relative flex items-center">
                        <input type="password"
                               name="password"
                               id="password"
                               required
                               autocomplete="current-password"
                               placeholder="Masukkan kata sandi"
                               class="w-full h-12 pl-4 pr-12 rounded-xl bg-base-200/50 border border-base-300 text-base-content placeholder-base-content/30 text-sm font-medium focus:outline-none focus:border-primary focus:bg-base-100 transition">

                        <!-- Tombol Show/Hide Password -->
                        <button type="button"
                                onclick="togglePassword()"
                                class="absolute right-0 top-0 h-12 w-12 flex items-center justify-center text-base-content/40 hover:text-base-content focus:outline-none cursor-pointer"
                                aria-label="Lihat kata sandi">
                            <!-- Mata Tertutup (default saat password tersembunyi) -->
                            <svg id="eye-closed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                            <!-- Mata Terbuka (saat password ditampilkan) -->
                            <svg id="eye-open" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Checkbox Ingat Saya -->
                <div class="pt-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                               name="remember"
                               id="remember"
                               class="w-4 h-4 rounded border-base-300 text-primary focus:ring-0 focus:outline-none">
                        <span class="text-xs font-medium text-base-content/70">Ingat saya</span>
                    </label>
                </div>

                <!-- Tombol Submit -->
                <button type="submit"
                        id="submitBtn"
                        class="w-full h-12 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/20 transition active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer mt-2">
                    <span>Masuk ke Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6 text-center">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-base-300"></div></div>
                <span class="relative px-3 bg-base-100 text-[11px] font-semibold text-base-content/40 uppercase tracking-wider">atau</span>
            </div>

            <!-- Link Daftar -->
            <a href="{{ route('register') }}"
               class="w-full h-12 bg-base-200 hover:bg-base-300 text-base-content text-xs sm:text-sm font-bold rounded-xl transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>Daftar Gratis 14 Hari</span>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full text-center py-4 text-xs text-base-content/40">
        &copy; {{ date('Y') }} ANDstudio. Semua hak dilindungi.
    </footer>

    <script>
        // Toggle Password
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeClosed.classList.add('hidden');
                eyeOpen.classList.remove('hidden');
            } else {
                pwd.type = 'password';
                eyeClosed.classList.remove('hidden');
                eyeOpen.classList.add('hidden');
            }
        }

        // Toggle Theme
        function updateThemeIcon(isLight) {
            const sun = document.getElementById('theme-icon-sun');
            const moon = document.getElementById('theme-icon-moon');
            if (sun && moon) {
                sun.classList.toggle('hidden', !isLight);
                moon.classList.toggle('hidden', isLight);
            }
        }

        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'studio-dark';
            const nextTheme = isDark ? 'studio-light' : 'studio-dark';
            html.setAttribute('data-theme', nextTheme);
            localStorage.setItem('theme', nextTheme === 'studio-light' ? 'light' : 'dark');
            updateThemeIcon(!isDark);
        }

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                const isLight = savedTheme === 'light';
                document.documentElement.setAttribute('data-theme', isLight ? 'studio-light' : 'studio-dark');
                updateThemeIcon(isLight);
            }

            // Remember me
            const savedEmail = localStorage.getItem('rememberedEmail');
            const emailInput = document.getElementById('email');
            const remCheckbox = document.getElementById('remember');
            if (savedEmail && emailInput) {
                emailInput.value = savedEmail;
                if (remCheckbox) remCheckbox.checked = true;
            }
        });

        // Simpan Email saat Remember Me dicentang
        document.getElementById('loginForm')?.addEventListener('submit', function() {
            const remCheckbox = document.getElementById('remember');
            const emailInput = document.getElementById('email');
            if (remCheckbox?.checked && emailInput?.value) {
                localStorage.setItem('rememberedEmail', emailInput.value);
            } else {
                localStorage.removeItem('rememberedEmail');
            }

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>Memproses...</span>';
        });
    </script>
</body>
</html>
