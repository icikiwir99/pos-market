@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="archive" class="w-7 h-7 text-blue-600"></i>
                LAPORAN VALUASI NILAI ASET INVENTARIS
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Audit akumulasi nilai modal beli vs proyeksi nilai penjualan seluruh barang di toko</p>
        </div>

        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Cetak Valuasi</span>
        </button>
    </div>

    <!-- 3 Big Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Modal Nilai Stok (Harga Beli)</span>
            <span class="text-2xl font-black text-slate-900 font-mono mt-1 block">Rp {{ number_format($totalStockValueBuy, 0, ',', '.') }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Investasi aset stok fisik di toko</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-blue-700 uppercase tracking-wider block">Proyeksi Nilai Penjualan (Harga Jual)</span>
            <span class="text-2xl font-black text-blue-700 font-mono mt-1 block">Rp {{ number_format($totalStockValueSell, 0, ',', '.') }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Nilai omset bila semua stok terjual</span>
        </div>

        <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-200 shadow-sm">
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Potensi Margin Keuntungan Kotor</span>
            <span class="text-2xl font-black text-emerald-600 font-mono mt-1 block">
                Rp {{ number_format($totalStockValueSell - $totalStockValueBuy, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-emerald-700 font-semibold mt-1 block">
                Margin rata-rata: {{ $totalStockValueBuy > 0 ? round((($totalStockValueSell - $totalStockValueBuy) / $totalStockValueBuy) * 100, 1) : 0 }}%
            </span>
        </div>
    </div>

    <!-- Stock Valuation Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Rincian Nilai Stok Per Produk</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-center">Stok</th>
                        <th class="py-3 px-4 text-right">Harga Beli</th>
                        <th class="py-3 px-4 text-right">Total Nilai Modal</th>
                        <th class="py-3 px-4 text-right">Harga Jual</th>
                        <th class="py-3 px-4 text-right">Total Nilai Jual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $p)
                    @php
                        $itemBuyTotal = $p->stock * $p->buy_price;
                        $itemSellTotal = $p->stock * $p->sell_price;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4">
                            <span class="font-bold text-slate-900 block">{{ $p->name }}</span>
                            <span class="font-mono text-[10px] text-slate-400 block">{{ $p->barcode }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $p->category->name }}</td>
                        <td class="py-3 px-4 text-center font-mono font-bold">
                            <span class="px-2 py-0.5 rounded {{ $p->stock <= $p->min_stock ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-800' }}">
                                {{ $p->stock }} {{ $p->unit }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-600">Rp {{ number_format($p->buy_price, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">Rp {{ number_format($itemBuyTotal, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono text-blue-700">Rp {{ number_format($p->sell_price, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-blue-800">Rp {{ number_format($itemSellTotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $products->links() }}
        </div>
    </div>

</div>
@endsection
