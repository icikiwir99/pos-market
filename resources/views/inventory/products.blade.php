@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{
    createModal: false,
    editModal: false,
    selectedProduct: null,
    openEdit(prod) {
        this.selectedProduct = prod;
        this.editModal = true;
    }
}">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="package" class="w-7 h-7 text-blue-600"></i>
                MASTER DATA PRODUK
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Katalog seluruh barang dagangan retail, harga beli, harga jual, dan stok gudang</p>
        </div>

        <button @click="createModal = true" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Produk Baru</span>
        </button>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6">
        <form action="{{ route('inventory.products') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <!-- Search Query -->
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama barang, barcode, SKU..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
            </div>

            <!-- Category Filter -->
            <div class="sm:col-span-3">
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Level Filter -->
            <div class="sm:col-span-2">
                <select name="stock_filter" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
                    <option value="">Semua Status Stok</option>
                    <option value="low" {{ $stockFilter === 'low' ? 'selected' : '' }}>Stok Kritis (Low)</option>
                    <option value="empty" {{ $stockFilter === 'empty' ? 'selected' : '' }}>Stok Habis (0)</option>
                </select>
            </div>

            <!-- Submit Filter -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-3 rounded-xl text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('inventory.products') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>

        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Barcode & SKU</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-right">Harga Beli</th>
                        <th class="py-3 px-4 text-right">Harga Jual</th>
                        <th class="py-3 px-4 text-right">Margin / Profit</th>
                        <th class="py-3 px-4 text-center">Stok Fisik</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $p)
                    @php
                        $margin = $p->sell_price - $p->buy_price;
                        $marginPct = $p->buy_price > 0 ? round(($margin / $p->buy_price) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-mono font-bold text-slate-900 block">{{ $p->barcode }}</span>
                            <span class="text-[10px] font-mono text-slate-400 block">{{ $p->sku }}</span>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            <div>{{ $p->name }}</div>
                            <span class="text-[10px] text-slate-400 font-normal">Satuan: {{ $p->unit }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full text-[10px] font-bold">
                                {{ $p->category->name }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-600">
                            Rp {{ number_format($p->buy_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-blue-700">
                            Rp {{ number_format($p->sell_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-emerald-600 font-bold">
                            Rp {{ number_format($margin, 0, ',', '.') }}
                            <span class="text-[10px] block font-normal">({{ $marginPct }}%)</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="font-mono font-bold px-2 py-0.5 rounded text-xs {{ $p->stock <= $p->min_stock ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-800' }}">
                                {{ $p->stock }} {{ $p->unit }}
                            </span>
                            @if($p->stock <= $p->min_stock)
                                <span class="block text-[9px] text-rose-600 font-extrabold uppercase mt-0.5">Min: {{ $p->min_stock }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button type="button" @click="openEdit({{ json_encode($p) }})"
                                    class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors font-bold inline-flex items-center gap-1">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Edit</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400">
                            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                            <p class="text-xs">Tidak ada data produk yang ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-200">
            {{ $products->links() }}
        </div>
    </div>

    <!-- MODAL TAMBAH PRODUK -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden" @click.outside="createModal = false">
            <div class="bg-blue-600 text-white px-5 py-3.5 flex items-center justify-between">
                <h3 class="text-sm font-extrabold flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-amber-300"></i>
                    TAMBAH PRODUK RETAIL BARU
                </h3>
                <button @click="createModal = false" class="text-white/80 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form action="{{ route('inventory.products.store') }}" method="POST" class="p-5 space-y-3.5">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Barcode (EAN-13)</label>
                        <input type="text" name="barcode" required placeholder="899..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kode SKU</label>
                        <input type="text" name="sku" required placeholder="MNM-101"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nama Produk</label>
                    <input type="text" name="name" required placeholder="Contoh: Indowowok Air Mineral 600ml"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kategori</label>
                        <select name="category_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Satuan Unit</label>
                        <input type="text" name="unit" required value="pcs" placeholder="pcs, botol, box, pack"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Harga Beli Modal (Rp)</label>
                        <input type="number" name="buy_price" required min="0" step="100" placeholder="0"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Harga Jual Kasir (Rp)</label>
                        <input type="number" name="sell_price" required min="0" step="100" placeholder="0"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold text-blue-700 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Stok Awal Fisik</label>
                        <input type="number" name="stock" required min="0" value="50"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Batas Minimum Stok (Alert)</label>
                        <input type="number" name="min_stock" required min="1" value="10"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider shadow-md">
                        Simpan Produk ke Inventaris
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PRODUK -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden" @click.outside="editModal = false">
            <div class="bg-slate-900 text-white px-5 py-3.5 flex items-center justify-between">
                <h3 class="text-sm font-extrabold flex items-center gap-2">
                    <i data-lucide="edit" class="w-4 h-4 text-blue-400"></i>
                    EDIT DATA PRODUK
                </h3>
                <button @click="editModal = false" class="text-white/80 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form :action="'{{ url('/inventory/products') }}/' + selectedProduct?.id" method="POST" class="p-5 space-y-3.5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Barcode</label>
                        <input type="text" name="barcode" :value="selectedProduct?.barcode" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kode SKU</label>
                        <input type="text" name="sku" :value="selectedProduct?.sku" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nama Produk</label>
                    <input type="text" name="name" :value="selectedProduct?.name" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kategori</label>
                        <select name="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" :selected="selectedProduct?.category_id == {{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Satuan Unit</label>
                        <input type="text" name="unit" :value="selectedProduct?.unit" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Harga Beli Modal (Rp)</label>
                        <input type="number" name="buy_price" :value="selectedProduct?.buy_price" required min="0" step="100"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Harga Jual Kasir (Rp)</label>
                        <input type="number" name="sell_price" :value="selectedProduct?.sell_price" required min="0" step="100"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold text-blue-700">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Batas Minimum Stok (Alert)</label>
                    <input type="number" name="min_stock" :value="selectedProduct?.min_stock" required min="1"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="edit_is_active" :checked="selectedProduct?.is_active" class="w-4 h-4 text-blue-600 rounded">
                    <label for="edit_is_active" class="text-xs font-semibold text-slate-700">Produk Aktif Dijual di Kasir</label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider shadow-md">
                        Perbarui Data Produk
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
