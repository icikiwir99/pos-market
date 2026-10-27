<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS Indomarut</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-100 via-blue-50 to-slate-200 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full" x-data="{
        setDemo(user, pass) {
            document.getElementById('login').value = user;
            document.getElementById('password').value = pass;
            document.getElementById('login-form').submit();
        }
    }">
        <!-- Indowowok Brand Top Stripe -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
            <div class="h-2 w-full flex">
                <div class="bg-blue-600 flex-1"></div>
                <div class="bg-red-500 w-24"></div>
                <div class="bg-amber-400 w-24"></div>
            </div>

            <div class="p-8">
                <!-- Brand Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center bg-blue-600 text-white font-black text-2xl tracking-tighter px-4 py-2 rounded-xl shadow-md mb-3">
                        <span class="text-white">INDO</span><span class="text-amber-300">WOWOK</span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">POINT OF SALE & INVENTARIS</h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Masuk untuk mengakses sistem kasir & operasional toko</p>
                </div>

                <!-- Error Alert -->
                @if($errors->any())
                    <div class="mb-5 bg-rose-50 border-l-4 border-rose-500 p-3.5 rounded-r-lg">
                        <div class="flex items-center gap-2 text-rose-800 text-xs font-semibold">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-5 bg-emerald-50 border-l-4 border-emerald-500 p-3.5 rounded-r-lg">
                        <div class="flex items-center gap-2 text-emerald-800 text-xs font-semibold">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Login Form -->
                <form id="login-form" action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="login" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Username / Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <input type="text" id="login" name="login" required autofocus
                                   value="{{ old('login') }}"
                                   placeholder="Contoh: kasir / manager"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all font-medium">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi (Password)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input type="password" id="password" name="password" required
                                   placeholder="••••••••"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all font-medium">
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer text-slate-600 font-medium">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            <span>Ingat saya</span>
                        </label>
                        <span class="text-slate-400">Default: password</span>
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-4 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 text-sm mt-2">
                        <span>Masuk ke Sistem</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- Quick Demo Login Section -->
                <div class="mt-8 pt-6 border-t border-slate-200">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 text-center mb-3">1-Klik Akses Cepat (Demo Role)</p>
                    <div class="grid grid-cols-3 gap-2">
                        <!-- Kasir -->
                        <button type="button" @click="setDemo('kasir', 'password')"
                                class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-blue-200 bg-blue-50/60 hover:bg-blue-100 hover:border-blue-400 text-blue-800 transition-all text-center group">
                            <i data-lucide="shopping-cart" class="w-5 h-5 mb-1 group-hover:scale-110 transition-transform"></i>
                            <span class="text-xs font-bold">Kasir</span>
                            <span class="text-[10px] text-blue-600">Siti</span>
                        </button>

                        <!-- Gudang -->
                        <button type="button" @click="setDemo('gudang', 'password')"
                                class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-amber-200 bg-amber-50/60 hover:bg-amber-100 hover:border-amber-400 text-amber-900 transition-all text-center group">
                            <i data-lucide="boxes" class="w-5 h-5 mb-1 group-hover:scale-110 transition-transform"></i>
                            <span class="text-xs font-bold">Gudang</span>
                            <span class="text-[10px] text-amber-700">Doni</span>
                        </button>

                        <!-- Manager -->
                        <button type="button" @click="setDemo('manager', 'password')"
                                class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-purple-200 bg-purple-50/60 hover:bg-purple-100 hover:border-purple-400 text-purple-900 transition-all text-center group">
                            <i data-lucide="shield-check" class="w-5 h-5 mb-1 group-hover:scale-110 transition-transform"></i>
                            <span class="text-xs font-bold">Manager</span>
                            <span class="text-[10px] text-purple-700">Pak Budi</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-500 font-medium">
            POS Indowowok Desktop System • PT Indomarco Prismatama
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
