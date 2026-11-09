@extends('layouts.app')
// Fitur Diskon Kasir POS Indowowok
// Jawireng
@section('content')
<div class="max-w-[1600px] mx-auto px-2 sm:px-4 lg:px-6 py-2" x-data="posApp()" x-init="initPos()">

    <!-- Shift Warning Banner if Shift is Closed -->
    @if(!$activeShift)
    <div class="bg-amber-500 text-slate-900 px-4 py-3 rounded-xl mb-3 shadow-md flex items-center justify-between font-semibold animate-pulse">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 text-slate-950"></i>
            <span>Shift kasir belum dibuka! Silakan masukkan modal awal kasir untuk mulai bertransaksi.</span>
        </div>
        <button @click="openShiftModal = true" class="bg-slate-900 text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-slate-800 transition-colors flex items-center gap-1.5">
            <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
            <span>Buka Shift Sekarang</span>
        </button>
    </div>
    @endif

    <!-- Top Action Bar (Kasir Status & Shortcuts Helper) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 px-4 py-2.5 mb-3 flex flex-wrap items-center justify-between gap-3">
        <!-- Kasir Shift Details -->
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $activeShift ? 'bg-emerald-500 animate-ping' : 'bg-rose-500' }}"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Kasir: <strong class="text-slate-900">{{ auth()->user()->name }}</strong>
                </span>
            </div>
            <div class="h-4 w-px bg-slate-200"></div>
            @if($activeShift)
                <div class="text-xs text-slate-600 flex items-center gap-2">
                    <span>Kas Awal: <strong class="text-slate-900 font-mono">Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</strong></span>
                    <span>•</span>
                    {{-- FIX: Sebelumnya hanya "Kas Awal" yang ditampilkan sehingga kas tidak terlihat bertambah setelah transaksi.
                         Sekarang "Kas Saat Ini" (modal + penjualan tunai) ditampilkan dan di-update otomatis setelah checkout. --}}
                    <span>Kas Saat Ini: <strong class="text-emerald-700 font-mono" x-text="formatRupiah(currentCash)"></strong></span>
                    <span>•</span>
                    <span>Jam Buka: <span class="font-mono">{{ $activeShift->opened_at->format('H:i') }} WIB</span></span>
                </div>
            @endif
        </div>

        <!-- Shift & Actions Controls -->
        <div class="flex items-center gap-2">
            @if($activeShift)
                <button @click="closeShiftModal = true" 
                        class="px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5">
                    <i data-lucide="door-closed" class="w-3.5 h-3.5"></i>
                    <span>Tutup Shift (Rekap)</span>
                </button>
            @else
                <button @click="openShiftModal = true" 
                        class="px-3 py-1.5 bg-emerald-600 text-white hover:bg-emerald-700 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="key" class="w-3.5 h-3.5"></i>
                    <span>Buka Shift Kasir</span>
                </button>
            @endif

            <button @click="showHelpModal = true" 
                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-all flex items-center gap-1">
                <kbd class="bg-white px-1.5 py-0.5 rounded border border-slate-300 font-mono text-[10px]">F1</kbd>
                <span>Shortcut</span>
            </button>
        </div>
    </div>

    <!-- Main POS Grid Layout -->
    <div class="grid grid-cols-12 gap-3">

        <!-- Left Column: Search, Categories & Product Grid (7 Cols) -->
        <div class="col-span-12 lg:col-span-7 flex flex-col gap-3">
            
            <!-- Barcode & Search Input (F4 shortcut) -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3">
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-blue-600">
                        <i data-lucide="scan-barcode" class="w-5 h-5"></i>
                    </div>
                    <input type="text" 
                           id="barcode-input"
                           x-ref="barcodeInput"
                           x-model="searchQuery" 
                           @input.debounce.250ms="searchProducts()"
                           @keydown.enter.prevent="handleBarcodeEnter()"
                           placeholder="Scan Barcode atau Cari Produk... (Tekan F4 untuk fokus)" 
                           class="w-full pl-11 pr-24 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all">
                    
                    <div class="absolute right-2 flex items-center gap-1">
                        <span class="bg-slate-200 text-slate-700 text-[10px] font-mono px-2 py-1 rounded font-bold">F4</span>
                        <button type="button" @click="searchQuery = ''; searchProducts()" x-show="searchQuery" class="text-slate-400 hover:text-slate-600 p-1">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Category Quick Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto pt-2.5 pb-1 text-xs">
                    <button @click="selectedCategory = null; searchProducts()" 
                            :class="selectedCategory === null ? 'bg-blue-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors flex-shrink-0">
                        Semua Produk
                    </button>
                    @foreach($categories as $cat)
                        <button @click="selectedCategory = {{ $cat->id }}; searchProducts()" 
                                :class="selectedCategory === {{ $cat->id }} ? 'bg-blue-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                                class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors flex-shrink-0">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Products Catalog Grid -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-2 px-1">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Katalog Produk Toko</span>
                    <span class="text-xs text-slate-500 font-mono" x-text="productsList.length + ' item ditemukan'"></span>
                </div>

                <!-- Products Grid Scrollable Container -->
                {{-- FIX: Kartu produk terpotong (harga tidak terlihat) karena baris grid ikut "menyusut" saat tinggi container dibatasi
                     (kartu memakai overflow-hidden sehingga tinggi minimumnya dianggap 0). Solusi: grid-auto-rows: max-content
                     + align-content: start agar tiap baris selalu setinggi isi kartu, dan tinggi area katalog mengikuti tinggi layar.
                     Memakai inline style supaya langsung berlaku tanpa perlu `npm run build`. --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 overflow-y-auto p-1"
                     style="grid-auto-rows: max-content; align-content: start; min-height: 320px; max-height: calc(100vh - 330px);">
                    <template x-for="product in productsList" :key="product.id">
                        <div @click="addToCart(product)"
                             class="flex flex-col justify-between p-2.5 min-h-[110px] bg-slate-50 hover:bg-blue-50/70 border border-slate-200 hover:border-blue-400 rounded-xl cursor-pointer transition-all hover:shadow-md select-none group text-left relative overflow-hidden">
                            
                            <!-- Low stock tag -->
                            <div x-show="product.stock <= product.min_stock" class="absolute top-1.5 right-1.5 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded uppercase">
                                Stok Kritis
                            </div>

                            <div>
                                <span class="text-[10px] font-mono text-slate-400 block truncate" x-text="product.barcode"></span>
                                <h3 class="text-xs font-bold text-slate-800 line-clamp-2 mt-0.5 group-hover:text-blue-700 leading-snug" x-text="product.name"></h3>
                            </div>

                            <div class="mt-2 pt-2 border-t border-slate-200/80 flex items-center justify-between gap-1.5">
                                <div class="min-w-0">
                                    {{-- FIX: whitespace-nowrap agar harga tidak terpecah/terpotong --}}
                                    <span class="text-xs font-extrabold text-blue-700 font-mono whitespace-nowrap" x-text="formatRupiah(product.sell_price)"></span>
                                    <span class="text-[10px] text-slate-500 block">/ <span x-text="product.unit"></span></span>
                                </div>
                                <span class="text-[11px] font-bold font-mono px-1.5 py-0.5 rounded whitespace-nowrap flex-shrink-0"
                                      :class="product.stock <= product.min_stock ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-700'"
                                      x-text="'Sisa: ' + product.stock">
                                </span>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="productsList.length === 0" class="flex flex-col items-center justify-center py-12 text-slate-400 text-center">
                    <i data-lucide="package-search" class="w-10 h-10 mb-2 opacity-50"></i>
                    <p class="text-xs font-medium">Tidak ada produk yang cocok dengan pencarian.</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Cart Drawer & Checkout Summary (5 Cols) -->
        <div class="col-span-12 lg:col-span-5 flex flex-col gap-3">
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col h-full">
                <!-- Cart Header -->
                <div class="p-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50 rounded-t-xl">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shopping-bag" class="w-5 h-5 text-blue-600"></i>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Keranjang Belanja</h2>
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full" x-text="cartTotalItems + ' item'"></span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <!-- Hold Cart Button (F7) -->
                        <button type="button" @click="holdCart()" 
                                title="Tahan Transaksi (F7)"
                                :disabled="cart.length === 0"
                                class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded text-xs font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1">
                            <i data-lucide="pause-circle" class="w-3.5 h-3.5"></i>
                            <span>Hold (F7)</span>
                        </button>

                        <!-- Recall Cart Button (F8) -->
                        <button type="button" @click="recallCart()" 
                                title="Buka Transaksi Tertahan (F8)"
                                x-show="heldCart !== null"
                                class="px-2 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded text-xs font-bold transition-all flex items-center gap-1 animate-bounce">
                            <i data-lucide="play-circle" class="w-3.5 h-3.5"></i>
                            <span>Recall (F8)</span>
                        </button>

                        <!-- Clear Cart Button (F9) -->
                        <button type="button" @click="clearCart()" 
                                title="Kosongkan Keranjang (F9)"
                                :disabled="cart.length === 0"
                                class="px-2 py-1 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded text-xs font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Customer Input -->
                <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                    <span class="text-xs font-medium text-slate-500 whitespace-nowrap">Pelanggan:</span>
                    <input type="text" x-model="customerName" placeholder="Nama Pelanggan (Default: Pelanggan Umum)"
                           class="w-full bg-white px-2.5 py-1 text-xs border border-slate-300 rounded font-medium focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2 max-h-[340px] min-h-[220px]">
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="p-2.5 bg-slate-50/70 hover:bg-blue-50/40 rounded-xl border border-slate-200/90 transition-all flex items-center justify-between gap-2">
                            <!-- Item Info -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-slate-800 truncate" x-text="item.name"></h4>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                    <span x-text="formatRupiah(item.sell_price)"></span> × <span class="font-bold text-slate-700" x-text="item.qty"></span>
                                </div>
                            </div>

                            <!-- Qty Controls -->
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button type="button" @click="decrementQty(index)" 
                                        class="w-6 h-6 rounded bg-white hover:bg-slate-200 text-slate-700 border border-slate-300 flex items-center justify-center font-black text-xs transition-colors">
                                    -
                                </button>
                                <span class="w-7 text-center font-bold font-mono text-xs text-slate-900" x-text="item.qty"></span>
                                <button type="button" @click="incrementQty(index)" 
                                        class="w-6 h-6 rounded bg-white hover:bg-slate-200 text-slate-700 border border-slate-300 flex items-center justify-center font-black text-xs transition-colors">
                                    +
                                </button>
                            </div>

                            <!-- Subtotal & Remove -->
                            <div class="text-right flex-shrink-0 min-w-[75px]">
                                <div class="text-xs font-black font-mono text-slate-900" x-text="formatRupiah(item.sell_price * item.qty)"></div>
                                <button type="button" @click="removeFromCart(index)" class="text-[10px] text-rose-500 hover:text-rose-700 hover:underline">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="flex flex-col items-center justify-center py-14 text-slate-400 text-center">
                        <i data-lucide="shopping-cart" class="w-10 h-10 mb-2 opacity-40"></i>
                        <p class="text-xs font-medium">Keranjang masih kosong.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Scan barcode atau klik produk di katalog</p>
                    </div>
                </div>

                <!-- Cart Calculation Summary -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 space-y-2 rounded-b-xl">
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Subtotal Belanja</span>
                        <span class="font-mono font-bold text-slate-800" x-text="formatRupiah(subtotal)"></span>
                    </div>

                    <div class="flex justify-between text-xs text-emerald-600" x-show="discountAmount > 0">
                        <span>Diskon / Promo</span>
                        <span class="font-mono font-bold" x-text="'- ' + formatRupiah(discountAmount)"></span>
                    </div>

                    <div class="flex justify-between text-xs text-slate-600">
                        <span>PPN (11%)</span>
                        <span class="font-mono font-bold text-slate-800" x-text="formatRupiah(taxAmount)"></span>
                    </div>

                    <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700">TOTAL BAYAR</span>
                            <span class="text-[10px] text-slate-400 block font-normal">Sudah termasuk PPN</span>
                        </div>
                        <span class="text-2xl font-black font-mono text-blue-700" x-text="formatRupiah(totalAmount)"></span>
                    </div>

                    <!-- Bayar Button (F2) -->
                    <button type="button" 
                            @click="openPaymentModal()"
                            :disabled="cart.length === 0 || !hasActiveShift"
                            class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-extrabold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-between text-sm disabled:opacity-40 disabled:cursor-not-allowed group mt-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                            <span>BAYAR SEKARANG</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono" x-text="formatRupiah(totalAmount)"></span>
                            <kbd class="bg-blue-800/80 text-white px-2 py-0.5 rounded text-[11px] font-mono">F2</kbd>
                        </div>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL 1: PEMBAYARAN (F2) -->
    <div x-show="paymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden" @click.outside="paymentModal = false">
            <!-- Modal Header -->
            <div class="bg-blue-600 text-white px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="badge-dollar-sign" class="w-5 h-5 text-amber-300"></i>
                    <h3 class="text-base font-extrabold tracking-tight">MODUL PEMBAYARAN</h3>
                </div>
                <button @click="paymentModal = false" class="text-white/80 hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <!-- Total Amount Banner -->
                <div class="bg-blue-50 border border-blue-200 p-3.5 rounded-xl flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-900">Total Tagihan:</span>
                    <span class="text-2xl font-black font-mono text-blue-700" x-text="formatRupiah(totalAmount)"></span>
                </div>

                <!-- Payment Method Tabs -->
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-blue-600 text-white font-bold shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                            class="py-2.5 px-3 rounded-xl text-xs transition-all flex flex-col items-center gap-1">
                        <i data-lucide="banknote" class="w-4 h-4"></i>
                        <span>TUNAI (CASH)</span>
                    </button>
                    <button type="button" @click="paymentMethod = 'qris'; paymentAmount = totalAmount"
                            :class="paymentMethod === 'qris' ? 'bg-blue-600 text-white font-bold shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                            class="py-2.5 px-3 rounded-xl text-xs transition-all flex flex-col items-center gap-1">
                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                        <span>QRIS STATIS/DINAMIS</span>
                    </button>
                    <button type="button" @click="paymentMethod = 'debit'; paymentAmount = totalAmount"
                            :class="paymentMethod === 'debit' ? 'bg-blue-600 text-white font-bold shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                            class="py-2.5 px-3 rounded-xl text-xs transition-all flex flex-col items-center gap-1">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <span>DEBIT / EDC</span>
                    </button>
                </div>

                <!-- Tab 1: Tunai Form -->
                <div x-show="paymentMethod === 'cash'" class="space-y-3 pt-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nominal Diterima</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">Rp</span>
                            <input type="number" x-ref="cashInput" x-model.number="paymentAmount" 
                                   @keydown.enter.prevent="processCheckout()"
                                   placeholder="0"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-lg font-mono font-bold focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
                        </div>
                    </div>

                    <!-- Quick Cash Buttons -->
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" @click="paymentAmount = totalAmount" class="px-2 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-800">
                            Uang Pas
                        </button>
                        <button type="button" @click="paymentAmount = 50000" class="px-2 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold font-mono text-slate-800">
                            50.000
                        </button>
                        <button type="button" @click="paymentAmount = 100000" class="px-2 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold font-mono text-slate-800">
                            100.000
                        </button>
                        <button type="button" @click="paymentAmount = 200000" class="px-2 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold font-mono text-slate-800">
                            200.000
                        </button>
                    </div>

                    <!-- Kembalian Calculation -->
                    <div class="p-3 rounded-xl border flex items-center justify-between"
                         :class="changeAmount >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200'">
                        <span class="text-xs font-bold uppercase tracking-wider" 
                              :class="changeAmount >= 0 ? 'text-emerald-900' : 'text-rose-900'">
                            Kembalian:
                        </span>
                        <span class="text-xl font-black font-mono"
                              :class="changeAmount >= 0 ? 'text-emerald-700' : 'text-rose-700'"
                              x-text="changeAmount >= 0 ? formatRupiah(changeAmount) : 'Kurang ' + formatRupiah(Math.abs(changeAmount))">
                        </span>
                    </div>
                </div>

                <!-- Tab 2: QRIS Mockup -->
                <div x-show="paymentMethod === 'qris'" class="text-center space-y-3 pt-2">
                    <div class="inline-block p-3 bg-white border-2 border-slate-300 rounded-2xl shadow-inner">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=INDOMARET_QRIS_DEMO" alt="QRIS Demo" class="w-40 h-40 mx-auto">
                    </div>
                    <div class="text-xs text-slate-600 font-medium">
                        Scan QRIS menggunakan BCA, Mandiri, GoPay, OVO, atau ShopeePay
                    </div>
                    <div class="bg-amber-50 text-amber-800 p-2 rounded-lg text-xs font-medium">
                        Simulasi pembayaran QRIS otomatis terverifikasi sistem saat Checkout
                    </div>
                </div>

                <!-- Tab 3: Kartu Debit / EDC -->
                <div x-show="paymentMethod === 'debit'" class="space-y-3 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Referensi EDC / Approval Code</label>
                        <input type="text" x-model="referenceNumber" placeholder="Contoh: REF98721389"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="button" 
                        @click="processCheckout()"
                        :disabled="isProcessing || (paymentMethod === 'cash' && changeAmount < 0)"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                    <span x-show="!isProcessing">SELESAIKAN PEMBAYARAN (ENTER)</span>
                    <span x-show="isProcessing">Memproses Transaksi...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: CETAK STRUK THERMAL (58mm / 80mm) -->
    <div x-show="receiptModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-sm w-full overflow-hidden" @click.outside="receiptModal = false">
            <div class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between">
                <span class="text-xs font-bold flex items-center gap-1.5 uppercase tracking-wider">
                    <i data-lucide="printer" class="w-4 h-4 text-amber-400"></i>
                    Struk Transaksi Selesai
                </span>
                <button @click="receiptModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Receipt Content (Thermal view) -->
            <div class="p-4 bg-slate-100">
                <div id="thermal-receipt" class="bg-white p-4 shadow-sm border border-slate-200 text-slate-900 text-xs font-mono mx-auto">
                    <!-- Indomaret Receipt Header -->
                    <div class="text-center border-b border-dashed border-slate-400 pb-2 mb-2 leading-tight">
                        <div class="font-black text-sm tracking-tight text-blue-900">INDOWOWOK</div>
                        <div class="text-[10px] text-slate-600">PT INDOMARCO PRISMATAMA</div>
                        <div class="text-[9px] text-slate-500">TOKO 084 - JL. RAYA UTAMA NO. 45</div>
                        <div class="text-[9px] text-slate-500">NPWP: 01.324.567.8-091.000</div>
                    </div>

                    <!-- Info Meta -->
                    <div class="text-[10px] space-y-0.5 border-b border-dashed border-slate-400 pb-2 mb-2">
                        <div class="flex justify-between">
                            <span>No. Bon:</span>
                            <span class="font-bold" x-text="lastTransaction?.invoice_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Waktu:</span>
                            <span x-text="lastTransaction?.created_at ? new Date(lastTransaction.created_at).toLocaleString('id-ID') : '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Kasir:</span>
                            <span x-text="lastTransaction?.cashier?.name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pelanggan:</span>
                            <span x-text="lastTransaction?.customer_name"></span>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="border-b border-dashed border-slate-400 pb-2 mb-2 space-y-1">
                        <template x-for="item in (lastTransaction?.items || [])" :key="item.id">
                            <div>
                                <div class="font-bold text-[11px] truncate" x-text="item.product_name"></div>
                                <div class="flex justify-between text-[10px] text-slate-600">
                                    <span x-text="item.qty + ' x ' + formatRupiah(item.unit_price)"></span>
                                    <span class="font-bold text-slate-900" x-text="formatRupiah(item.subtotal)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Summary Totals -->
                    <div class="text-[11px] space-y-1 border-b border-dashed border-slate-400 pb-2 mb-2">
                        <div class="flex justify-between">
                            <span>Total Gross:</span>
                            <span x-text="formatRupiah(lastTransaction?.subtotal || 0)"></span>
                        </div>
                        <div class="flex justify-between text-rose-600" x-show="lastTransaction?.discount_amount > 0">
                            <span>Potongan Diskon:</span>
                            <span x-text="'- ' + formatRupiah(lastTransaction?.discount_amount || 0)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>PPN (11%):</span>
                            <span x-text="formatRupiah(lastTransaction?.tax_amount || 0)"></span>
                        </div>
                        <div class="flex justify-between font-black text-xs pt-1 border-t border-slate-300">
                            <span>TOTAL NET:</span>
                            <span x-text="formatRupiah(lastTransaction?.total_amount || 0)"></span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span>Metode:</span>
                            <span class="uppercase font-bold" x-text="lastTransaction?.payment_method"></span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span>Bayar:</span>
                            <span x-text="formatRupiah(lastTransaction?.payment_amount || 0)"></span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span>Kembali:</span>
                            <span x-text="formatRupiah(lastTransaction?.change_amount || 0)"></span>
                        </div>
                    </div>

                    <!-- Receipt Footer -->
                    <div class="text-center text-[9px] text-slate-500 pt-1 space-y-0.5">
                        <div>TERIMA KASIH ATAS KUNJUNGAN ANDA</div>
                        <div>KRITIK & SARAN: 1500-280</div>
                        <div>WWW.INDOWOWOK.CO.ID</div>
                    </div>
                </div>
            </div>

            <!-- Receipt Actions -->
            <div class="p-3 bg-white border-t border-slate-200 flex gap-2">
                <button type="button" @click="printReceipt()"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-md">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Struk Thermal</span>
                </button>
                <button type="button" @click="receiptModal = false"
                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: BUKA SHIFT KASIR -->
    <div x-show="openShiftModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden" @click.outside="openShiftModal = false">
            <div class="bg-blue-600 text-white px-5 py-3.5 flex items-center justify-between">
                <h3 class="text-sm font-extrabold flex items-center gap-2">
                    <i data-lucide="key-round" class="w-4 h-4 text-amber-300"></i>
                    BUKA SHIFT KASIR
                </h3>
                <button @click="openShiftModal = false" class="text-white/80 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form action="{{ route('pos.shift.open') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Modal Awal Kas Masuk (Rp)</label>
                    <input type="number" name="starting_cash" value="200000" required min="0" step="1000"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-mono text-base font-bold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">Uang modal receh/pecahan kasir di laci uang kasir saat mulai shift.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Shift (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Shift Pagi Kasir 1"
                           class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-wider shadow-md">
                    Buka Shift & Mulai Kasir
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL 4: TUTUP SHIFT KASIR (REKAP KAS FISIK) -->
    <div x-show="closeShiftModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden" @click.outside="closeShiftModal = false">
            <div class="bg-rose-600 text-white px-5 py-3.5 flex items-center justify-between">
                <h3 class="text-sm font-extrabold flex items-center gap-2">
                    <i data-lucide="door-closed" class="w-4 h-4 text-white"></i>
                    TUTUP SHIFT KASIR & AUDIT KAS
                </h3>
                <button @click="closeShiftModal = false" class="text-white/80 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form action="{{ route('pos.shift.close') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-xs space-y-1">
                    <div class="flex justify-between text-slate-600">
                        <span>Modal Awal Kas:</span>
                        <span class="font-mono font-bold">Rp {{ number_format($activeShift?->starting_cash ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Total Kas Sistem Tercatat:</span>
                        {{-- FIX: nilai ini dulu statis (dirender saat halaman dimuat) sehingga tidak ikut berubah setelah transaksi tanpa refresh --}}
                        <span class="font-mono font-bold text-blue-700" x-text="formatRupiah(currentCash)"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Uang Fisik Dihitung di Laci (Rp)</label>
                    <input type="number" name="actual_cash" required min="0" step="any"
                           :value="currentCash"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-mono text-base font-bold focus:ring-2 focus:ring-rose-600 focus:outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">Hitung semua uang tunai fisik yang ada di kasir saat ini.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Penutupan Shift</label>
                    <textarea name="notes" rows="2" placeholder="Catatan selisih kas bila ada..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-600 focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-wider shadow-md">
                    Konfirmasi Tutup Shift Kasir
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL 5: PANDUAN SHORTCUT KEYBOARD (F1) -->
    <div x-show="showHelpModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden" @click.outside="showHelpModal = false">
            <div class="bg-slate-900 text-white px-5 py-3.5 flex items-center justify-between">
                <h3 class="text-sm font-extrabold flex items-center gap-2">
                    <i data-lucide="keyboard" class="w-4 h-4 text-amber-400"></i>
                    PANDUAN SHORTCUT KEYBOARD KASIR
                </h3>
                <button @click="showHelpModal = false" class="text-white/80 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="p-5 space-y-2.5 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="font-bold text-slate-700">F1</span>
                    <span class="text-slate-600">Buka panduan shortcut ini</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="font-bold text-slate-700">F2</span>
                    <span class="text-slate-600">Buka modal pembayaran (Checkout)</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="font-bold text-slate-700">F4</span>
                    <span class="text-slate-600">Fokus ke kolom Scan Barcode / Cari Produk</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="font-bold text-slate-700">F7</span>
                    <span class="text-slate-600">Tahan keranjang belanja aktif (Hold)</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="font-bold text-slate-700">F8</span>
                    <span class="text-slate-600">Ambil kembali transaksi tertahan (Recall)</span>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                    <span class="font-bold text-slate-700">F9</span>
                    <span class="text-slate-600">Kosongkan semua item di keranjang</span>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="font-bold text-slate-700">ENTER</span>
                    <span class="text-slate-600">Konfirmasi / Masukkan Barcode / Bayar</span>
                </div>
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-200 text-center">
                <button @click="showHelpModal = false" class="px-5 py-2 bg-slate-900 text-white rounded-lg text-xs font-bold">
                    Mengerti
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function posApp() {
    return {
        searchQuery: '',
        selectedCategory: null,
        productsList: @json($products),
        // FIX: status shift & kas saat ini dari server, dipakai untuk memblokir pembayaran & menampilkan kas live
        hasActiveShift: @json((bool) $activeShift),
        currentCash: {{ (float) ($activeShift?->ending_cash_expected ?? 0) }},
        cart: [],
        heldCart: null,
        customerName: 'Pelanggan Umum',
        
        // Modals
        paymentModal: false,
        receiptModal: false,
        openShiftModal: false,
        closeShiftModal: false,
        showHelpModal: false,
        
        // Checkout state
        paymentMethod: 'cash',
        paymentAmount: 0,
        referenceNumber: '',
        isProcessing: false,
        lastTransaction: null,

        initPos() {
            // Register Global Keyboard Shortcuts
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F1') {
                    e.preventDefault();
                    this.showHelpModal = !this.showHelpModal;
                } else if (e.key === 'F2') {
                    e.preventDefault();
                    if (this.cart.length > 0) this.openPaymentModal(); // guard shift ada di openPaymentModal()
                } else if (e.key === 'F4') {
                    e.preventDefault();
                    this.$refs.barcodeInput?.focus();
                } else if (e.key === 'F7') {
                    e.preventDefault();
                    if (this.cart.length > 0) this.holdCart();
                } else if (e.key === 'F8') {
                    e.preventDefault();
                    if (this.heldCart) this.recallCart();
                } else if (e.key === 'F9') {
                    e.preventDefault();
                    if (this.cart.length > 0) this.clearCart();
                }
            });

            // Auto focus barcode input
            this.$nextTick(() => {
                this.$refs.barcodeInput?.focus();
            });
        },

        searchProducts() {
            const params = new URLSearchParams();
            if (this.searchQuery) params.append('q', this.searchQuery);
            if (this.selectedCategory) params.append('category_id', this.selectedCategory);

            fetch(`{{ route('pos.search') }}?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    this.productsList = data;
                })
                .catch(err => console.error(err));
        },

        handleBarcodeEnter() {
            if (!this.searchQuery) return;

            // Direct barcode match
            const exactProduct = this.productsList.find(p => 
                p.barcode === this.searchQuery.trim() || p.sku.toLowerCase() === this.searchQuery.trim().toLowerCase()
            );

            if (exactProduct) {
                this.addToCart(exactProduct);
                this.searchQuery = '';
                this.searchProducts();
            } else if (this.productsList.length === 1) {
                this.addToCart(this.productsList[0]);
                this.searchQuery = '';
                this.searchProducts();
            }
        },

        addToCart(product) {
            if (product.stock <= 0) {
                alert(`Maaf, stok ${product.name} telah habis!`);
                return;
            }

            const existing = this.cart.find(item => item.id === product.id);
            if (existing) {
                if (existing.qty + 1 > product.stock) {
                    alert(`Tidak dapat menambah qty. Stok tersisa hanya ${product.stock}`);
                    return;
                }
                existing.qty++;
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    barcode: product.barcode,
                    sell_price: parseFloat(product.sell_price),
                    stock: product.stock,
                    unit: product.unit,
                    qty: 1
                });
            }
        },

        incrementQty(index) {
            const item = this.cart[index];
            if (item.qty + 1 > item.stock) {
                alert(`Maksimal stok tercapai (${item.stock})`);
                return;
            }
            item.qty++;
        },

        decrementQty(index) {
            if (this.cart[index].qty > 1) {
                this.cart[index].qty--;
            } else {
                this.removeFromCart(index);
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            if (confirm('Apakah Anda yakin ingin mengosongkan keranjang belanja?')) {
                this.cart = [];
            }
        },

        holdCart() {
            this.heldCart = {
                items: JSON.parse(JSON.stringify(this.cart)),
                customerName: this.customerName,
            };
            this.cart = [];
            alert('Transaksi berhasil ditahan (Hold). Anda dapat melayani transaksi lain sekarang.');
        },

        recallCart() {
            if (!this.heldCart) return;
            this.cart = this.heldCart.items;
            this.customerName = this.heldCart.customerName;
            this.heldCart = null;
        },

        get cartTotalItems() {
            return this.cart.reduce((total, item) => total + item.qty, 0);
        },

        get subtotal() {
            return this.cart.reduce((total, item) => total + (item.sell_price * item.qty), 0);
        },

        get discountAmount() {
            // Auto promo check: if subtotal >= 100,000, discount 10,000
            if (this.subtotal >= 100000) {
                return 10000;
            }
            return 0;
        },

        get taxAmount() {
            // 11% PPN on subtotal after discount
            return Math.round((this.subtotal - this.discountAmount) * 0.11);
        },

        get totalAmount() {
            return (this.subtotal - this.discountAmount) + this.taxAmount;
        },

        get changeAmount() {
            return (this.paymentAmount || 0) - this.totalAmount;
        },

        openPaymentModal() {
            // FIX: Blokir pembayaran (tombol & shortcut F2) jika shift belum dibuka
            if (!this.hasActiveShift) {
                alert('Shift kasir belum dibuka! Silakan buka shift (masukkan modal awal) terlebih dahulu.');
                this.openShiftModal = true;
                return;
            }
            this.paymentAmount = this.totalAmount;
            this.paymentMethod = 'cash';
            this.paymentModal = true;
            this.$nextTick(() => {
                this.$refs.cashInput?.select();
            });
        },

        processCheckout() {
            if (this.isProcessing) return;

            // FIX: Guard tambahan di sisi klien (validasi utama tetap di server: PosController@checkout)
            if (!this.hasActiveShift) {
                alert('Shift kasir belum dibuka! Pembayaran tidak dapat diproses.');
                this.paymentModal = false;
                this.openShiftModal = true;
                return;
            }

            if (this.paymentMethod === 'cash' && this.changeAmount < 0) {
                alert('Nominal uang tunai yang diterima kurang dari total belanja.');
                return;
            }

            this.isProcessing = true;

            const payload = {
                items: this.cart.map(item => ({ id: item.id, qty: item.qty })),
                payment_method: this.paymentMethod,
                payment_amount: this.paymentAmount,
                customer_name: this.customerName,
                reference_number: this.referenceNumber,
            };

            fetch('{{ route("pos.checkout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                this.isProcessing = false;
                if (data.success) {
                    // FIX: update kas saat ini dengan nilai terbaru dari server
                    if (typeof data.expected_cash !== 'undefined') {
                        this.currentCash = parseFloat(data.expected_cash);
                    }
                    this.lastTransaction = data.transaction;
                    this.paymentModal = false;
                    this.receiptModal = true;
                    this.cart = [];
                    this.customerName = 'Pelanggan Umum';
                    this.searchProducts(); // refresh stocks
                } else {
                    alert(data.message || 'Terjadi kesalahan saat memproses transaksi.');
                }
            })
            .catch(err => {
                this.isProcessing = false;
                console.error(err);
                alert('Gagal menghubungi server. Silakan coba lagi.');
            });
        },

        printReceipt() {
            window.print();
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number || 0);
        }
    };
}
</script>
@endpush