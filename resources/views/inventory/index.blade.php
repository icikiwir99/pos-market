@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="boxes" class="w-7 h-7 text-blue-600"></i>
                DASHBOARD INVENTARIS GUDANG
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Pantau ketersediaan stok produk, peringatan batas minimum, dan tanggal kedaluwarsa</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.restock') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span>Barang Masuk (Restock)</span>
            </a>
            <a href="{{ route('inventory.adjustments') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                <span>Penyesuaian Stok</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Total SKU -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Master Produk</span>
                <span class="text-3xl font-black text-slate-900 font-mono mt-1 block">{{ $totalProducts }}</span>
                <span class="text-[11px] text-slate-400 mt-1 block">SKU aktif terdaftar</span>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 2: Low Stock Warning -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider block">Stok Kritis (Low Stock)</span>
                <span class="text-3xl font-black text-amber-600 font-mono mt-1 block">{{ $lowStockProducts }}</span>
                <a href="{{ route('inventory.products', ['stock_filter' => 'low']) }}" class="text-[11px] text-blue-600 hover:underline font-bold mt-1 inline-block">
                    Lihat produk menipis →
                </a>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 3: Out of Stock -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-rose-600 uppercase tracking-wider block">Stok Habis (Kosong)</span>
                <span class="text-3xl font-black text-rose-600 font-mono mt-1 block">{{ $outOfStockProducts }}</span>
                <span class="text-[11px] text-rose-400 mt-1 block">Perlu order ke supplier</span>
            </div>
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center">
                <i data-lucide="x-circle" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 4: Expiring in 30 days -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-purple-600 uppercase tracking-wider block">Mendekati Kedaluwarsa</span>
                <span class="text-3xl font-black text-purple-600 font-mono mt-1 block">{{ $expiringBatches->count() }}</span>
                <span class="text-[11px] text-slate-400 mt-1 block">Dalam 30 hari ke depan</span>
            </div>
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                <i data-lucide="calendar-alert" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Expiring Batches Alert Section -->
    @if($expiringBatches->count() > 0)
    <div class="bg-white rounded-2xl border border-purple-200 shadow-sm overflow-hidden mb-6">
        <div class="bg-purple-50 px-5 py-3.5 border-b border-purple-100 flex items-center justify-between">
            <div class="flex items-center gap-2 text-purple-900 font-bold text-xs uppercase tracking-wider">
                <i data-lucide="clock-alert" class="w-4 h-4 text-purple-600"></i>
                <span>PERINGATAN PRODUK MENDEKATI TANGGAL KEDALUWARSA (BATCH EXPIRY)</span>
            </div>
            <span class="bg-purple-200 text-purple-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                {{ $expiringBatches->count() }} Batch
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Nomor Batch</th>
                        <th class="py-3 px-4">Tanggal Expired</th>
                        <th class="py-3 px-4">Sisa Hari</th>
                        <th class="py-3 px-4 text-right">Stok Batch</th>
                        <th class="py-3 px-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($expiringBatches as $batch)
                    @php
                        $daysLeft = \Carbon\Carbon::now()->diffInDays($batch->expiry_date, false);
                    @endphp
                    <tr class="hover:bg-purple-50/30">
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $batch->product->name }}</td>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $batch->batch_number }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-rose-600">{{ $batch->expiry_date->format('d M Y') }}</td>
                        <td class="py-3 px-4">
                            @if($daysLeft <= 0)
                                <span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase">Sudah Lewat</span>
                            @elseif($daysLeft <= 7)
                                <span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full text-[10px] font-extrabold">{{ $daysLeft }} Hari Lagi</span>
                            @else
                                <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $daysLeft }} Hari Lagi</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold">{{ $batch->stock }} {{ $batch->product->unit }}</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('inventory.adjustments') }}" class="text-blue-600 hover:text-blue-800 font-bold hover:underline">
                                Buat Penyesuaian →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- 2 Column Section: Recent Restocks vs Recent Adjustments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Column 1: Recent Restocks -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-blue-600"></i>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Penerimaan Barang Masuk Terbaru</h3>
                </div>
                <a href="{{ route('inventory.restock') }}" class="text-xs text-blue-600 hover:underline font-bold">Lihat Semua →</a>
            </div>
            <div class="p-4 space-y-3">
                @forelse($recentRestocks as $po)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-900 font-mono">{{ $po->invoice_number }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $po->supplier->name }} • {{ $po->purchase_date->format('d M Y') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold font-mono text-blue-700">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</div>
                            <span class="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-bold uppercase">Diterima</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada data barang masuk.</p>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Recent Stock Adjustments -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 text-purple-600"></i>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Penyesuaian Stok Terakhir</h3>
                </div>
                <a href="{{ route('inventory.adjustments') }}" class="text-xs text-blue-600 hover:underline font-bold">Lihat Semua →</a>
            </div>
            <div class="p-4 space-y-3">
                @forelse($recentAdjustments as $adj)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-900">{{ $adj->product->name }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Alasan: <strong class="capitalize">{{ $adj->reason }}</strong> • Oleh: {{ $adj->user->name }}</div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono text-xs font-extrabold px-2 py-0.5 rounded {{ $adj->type === 'in' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $adj->type === 'in' ? '+' : '-' }}{{ $adj->qty }} {{ $adj->product->unit }}
                            </span>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $adj->created_at->format('d/m H:i') }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada penyesuaian stok.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
