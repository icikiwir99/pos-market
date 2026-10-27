@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="trending-up" class="w-7 h-7 text-blue-600"></i>
                DASHBOARD EKSEKUTIF MANAGER
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Analitik penjualan real-time, tren omset mingguan, performa kasir, dan laba kotor</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('manager.reports.sales') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>Laporan Penjualan Lengkap</span>
            </a>
            <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span>Buka Layar Kasir</span>
            </a>
        </div>
    </div>

    <!-- 4 Metric KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <!-- Card 1: Omset Hari Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Omset Penjualan Hari Ini</span>
                <span class="text-2xl font-black text-blue-700 font-mono mt-1 block">Rp {{ number_format($todaySales, 0, ',', '.') }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ $todayCount }} transaksi berhasil</span>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 2: Laba Kotor Hari Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Estimasi Laba Kotor Hari Ini</span>
                <span class="text-2xl font-black text-emerald-600 font-mono mt-1 block">Rp {{ number_format($todayProfit, 0, ',', '.') }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">Margin (Harga Jual - Modal)</span>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                <i data-lucide="line-chart" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 3: Omset Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-purple-600 uppercase tracking-wider block">Omset Berjalan Bulan Ini</span>
                <span class="text-2xl font-black text-purple-700 font-mono mt-1 block">Rp {{ number_format($monthSales, 0, ',', '.') }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ date('F Y') }}</span>
            </div>
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                <i data-lucide="calendar" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 4: Low Stock Alert -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider block">Peringatan Stok Menipis</span>
                <span class="text-2xl font-black text-amber-600 font-mono mt-1 block">{{ $lowStockCount }} SKU</span>
                <a href="{{ route('inventory.products', ['stock_filter' => 'low']) }}" class="text-[11px] text-blue-600 hover:underline font-bold mt-0.5 inline-block">
                    Periksa stok gudang →
                </a>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Chart & Cashier Performance Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        
        <!-- Chart.js 7-Day Trend (8 Cols) -->
        <div class="lg:col-span-8 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-blue-600"></i>
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Tren Omset Penjualan (7 Hari Terakhir)</h3>
                </div>
                <span class="text-xs text-slate-400 font-mono">Grafik Harian Toko</span>
            </div>
            <div class="flex-1 min-h-[280px]">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Cashier Scorecard Today (4 Cols) -->
        <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-purple-600"></i>
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Performa Kasir Hari Ini</h3>
                </div>
            </div>
            
            <div class="space-y-3 flex-1 overflow-y-auto">
                @forelse($cashierPerformances as $cp)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-900">{{ $cp->cashier ? $cp->cashier->name : 'Kasir Nonaktif' }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $cp->total_trans }} struk transaksi</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold font-mono text-blue-700">Rp {{ number_format($cp->total_omset, 0, ',', '.') }}</div>
                            <span class="text-[10px] text-emerald-600 font-bold">Aktif Melayani</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-10">Belum ada transaksi kasir hari ini.</p>
                @endforelse
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('manager.reports.shifts') }}" class="text-xs text-blue-600 font-bold hover:underline flex items-center justify-between">
                    <span>Audit Lengkap Uang Shift Kasir</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- Top 10 Best Sellers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="trophy" class="w-5 h-5 text-amber-500"></i>
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">10 Produk Terlaris (Best Seller)</h3>
            </div>
            <span class="text-xs text-slate-400">Diurutkan berdasarkan kuantitas terjual</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 text-center">Peringkat</th>
                        <th class="py-3 px-4">Nama Produk Retail</th>
                        <th class="py-3 px-4 text-center">Total Terjual (Qty)</th>
                        <th class="py-3 px-4 text-right">Total Akumulasi Omset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($topProducts as $idx => $tp)
                    <tr class="hover:bg-amber-50/20">
                        <td class="py-3 px-4 text-center font-mono font-bold">
                            @if($idx === 0)
                                <span class="bg-amber-100 text-amber-800 w-6 h-6 rounded-full inline-flex items-center justify-center font-black">🥇 1</span>
                            @elseif($idx === 1)
                                <span class="bg-slate-200 text-slate-700 w-6 h-6 rounded-full inline-flex items-center justify-center font-black">🥈 2</span>
                            @elseif($idx === 2)
                                <span class="bg-amber-200/60 text-amber-900 w-6 h-6 rounded-full inline-flex items-center justify-center font-black">🥉 3</span>
                            @else
                                <span class="text-slate-500 font-bold">{{ $idx + 1 }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $tp->product_name }}</td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-slate-900">{{ $tp->total_qty }} pcs</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-blue-700">Rp {{ number_format($tp->total_sales, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-slate-400">
                            Belum ada data penjualan tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('salesTrendChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Omset Penjualan (Rp)',
                    data: @json($chartData),
                    borderColor: '#00509d',
                    backgroundColor: 'rgba(0, 80, 157, 0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointBackgroundColor: '#00509d',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Omset: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000) + 'k';
                            },
                            font: { size: 10 }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
@endpush
