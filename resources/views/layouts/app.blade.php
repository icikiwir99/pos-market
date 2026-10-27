<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'POS Indowowok' }} - Point of Sale & Inventory</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans antialiased text-slate-800">

    <!-- Top Indomaret Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <!-- Top color accent stripe (Indomaret Blue, Red, Yellow) -->
        <div class="h-1.5 w-full flex">
            <div class="bg-blue-600 flex-1"></div>
            <div class="bg-red-500 w-24"></div>
            <div class="bg-amber-400 w-24"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2.5 group">
                        <div class="bg-blue-600 text-white p-2 rounded-lg font-black tracking-tighter text-xl shadow-sm flex items-center justify-center leading-none">
                            <span class="text-white">INDO</span><span class="text-amber-300">WOWOK</span>
                        </div>
                        <div class="leading-tight">
                            <h1 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-1.5">
                                POS & INVENTARIS
                                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-semibold">Toko 084</span>
                            </h1>
                            <p class="text-xs text-slate-500 font-medium">Sistem Kasir & Manajemen Stok Terpadu</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Tabs based on Role -->
                @auth
                <nav class="hidden md:flex items-center space-x-1">
                    @if(auth()->user()->isKasir() || auth()->user()->isManager())
                        <a href="{{ route('pos.index') }}" 
                           class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors {{ request()->routeIs('pos.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                            <span>Kasir POS</span>
                        </a>
                    @endif

                    @if(auth()->user()->isGudang() || auth()->user()->isManager())
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" 
                                    class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors {{ request()->routeIs('inventory.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <i data-lucide="boxes" class="w-4 h-4"></i>
                                <span>Gudang & Stok</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-60"></i>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-40">
                                <a href="{{ route('inventory.index') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-400"></i>
                                    <span>Dashboard Stok</span>
                                </a>
                                <a href="{{ route('inventory.products') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>
                                    <span>Master Produk</span>
                                </a>
                                <a href="{{ route('inventory.restock') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="truck" class="w-4 h-4 text-slate-400"></i>
                                    <span>Barang Masuk (Restock)</span>
                                </a>
                                <a href="{{ route('inventory.adjustments') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="sliders-horizontal" class="w-4 h-4 text-slate-400"></i>
                                    <span>Penyesuaian Stok</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isManager())
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" 
                                    class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors {{ request()->routeIs('manager.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                                <span>Manajemen & Laporan</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-60"></i>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-40">
                                <a href="{{ route('manager.dashboard') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="trending-up" class="w-4 h-4 text-slate-400"></i>
                                    <span>Dashboard Analitik</span>
                                </a>
                                <a href="{{ route('manager.reports.sales') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="receipt" class="w-4 h-4 text-slate-400"></i>
                                    <span>Laporan Penjualan</span>
                                </a>
                                <a href="{{ route('manager.reports.shifts') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="history" class="w-4 h-4 text-slate-400"></i>
                                    <span>Audit Shift Kasir</span>
                                </a>
                                <a href="{{ route('manager.reports.stock') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="archive" class="w-4 h-4 text-slate-400"></i>
                                    <span>Laporan Nilai Stok</span>
                                </a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="{{ route('manager.users') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                                    <span>Kelola Karyawan</span>
                                </a>
                                <a href="{{ route('manager.promotions') }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                    <i data-lucide="tag" class="w-4 h-4 text-slate-400"></i>
                                    <span>Kelola Promo / Diskon</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </nav>

                <!-- User Profile & Status -->
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2 text-right">
                        <div class="hidden sm:block">
                            <div class="text-sm font-bold text-slate-900 leading-none">{{ auth()->user()->name }}</div>
                            <div class="text-xs font-semibold capitalize mt-1 inline-flex items-center gap-1">
                                @if(auth()->user()->isManager())
                                    <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider">Manager</span>
                                @elseif(auth()->user()->isGudang())
                                    <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider">Gudang</span>
                                @else
                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider">Kasir</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                title="Keluar / Logout"
                                class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-slate-200">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Global Notification Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg mb-4 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                    <p class="text-sm font-medium text-emerald-900">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg mb-4 shadow-sm">
                <div class="flex items-start space-x-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-rose-900">Perhatian:</p>
                        <ul class="text-sm text-rose-700 list-disc list-inside mt-1">
                            @if(session('error'))
                                <li>{{ session('error') }}</li>
                            @endif
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content Body -->
    <main class="flex-1 w-full pb-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>© {{ date('Y') }} PT Indomarco Prismatama - POS Indomaret System</span>
            <span class="flex items-center gap-1.5 font-mono text-slate-400">
                <span>Versi 1.0 (Desktop Edition)</span>
                <span>•</span>
                <span id="live-clock"></span>
            </span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            function updateClock() {
                const clockEl = document.getElementById('live-clock');
                if (clockEl) {
                    const now = new Date();
                    clockEl.innerText = now.toLocaleDateString('id-ID', {
                        weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
                    }) + ' ' + now.toLocaleTimeString('id-ID');
                }
            }
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
    @stack('scripts')
</body>
</html>
