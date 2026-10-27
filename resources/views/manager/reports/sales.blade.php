@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="receipt" class="w-7 h-7 text-blue-600"></i>
                LAPORAN REKAPITULASI PENJUALAN
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Filter transaksi berdasarkan rentang tanggal, nama kasir, dan metode pembayaran</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- CSV Export Button -->
            <a href="{{ route('manager.reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Export ke Excel (CSV)</span>
            </a>
            
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Laporan</span>
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6">
        <form action="{{ route('manager.reports.sales') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <!-- Start Date -->
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <!-- End Date -->
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none">
            </div>

            <!-- Cashier -->
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Pilih Kasir</label>
                <select name="cashier_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    <option value="">Semua Kasir</option>
                    @foreach($allCashiers as $c)
                        <option value="{{ $c->id }}" {{ $cashierId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Method -->
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Metode Bayar</label>
                <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    <option value="">Semua</option>
                    <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="qris" {{ $paymentMethod === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="debit" {{ $paymentMethod === 'debit' ? 'selected' : '' }}>Debit</option>
                </select>
            </div>

            <!-- Action -->
            <div class="sm:col-span-1">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-xl text-xs transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Subtotal Kotor</span>
            <span class="text-xl font-black text-slate-900 font-mono mt-1 block">Rp {{ number_format($totalGross, 0, ',', '.') }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block">Total Diskon Diberikan</span>
            <span class="text-xl font-black text-rose-600 font-mono mt-1 block">- Rp {{ number_format($totalDiscount, 0, ',', '.') }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total PPN Terkumpul (11%)</span>
            <span class="text-xl font-black text-slate-900 font-mono mt-1 block">Rp {{ number_format($totalTax, 0, ',', '.') }}</span>
        </div>
        <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 shadow-sm">
            <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider block">Total Pendapatan Bersih (Net)</span>
            <span class="text-xl font-black text-blue-700 font-mono mt-1 block">Rp {{ number_format($totalNet, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Transactions List Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Daftar Struk Transaksi Penjualan</h3>
            <span class="text-xs text-slate-500 font-mono">{{ $transactions->total() }} transaksi ditemukan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Invoice</th>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4 text-center">Metode</th>
                        <th class="py-3 px-4 text-center">Items</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                        <th class="py-3 px-4 text-right">Diskon</th>
                        <th class="py-3 px-4 text-right">Total Net</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $t->invoice_number }}</td>
                        <td class="py-3 px-4 font-mono text-slate-600">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 text-slate-800 font-semibold">{{ $t->cashier ? $t->cashier->name : '-' }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $t->customer_name }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $t->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $t->payment_method }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-slate-700">
                            {{ $t->items->sum('qty') }} pcs
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-600">Rp {{ number_format($t->subtotal, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono text-rose-600">
                            {{ $t->discount_amount > 0 ? '- Rp ' . number_format($t->discount_amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-blue-700">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('pos.receipt', $t->id) }}" target="_blank" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg inline-flex items-center gap-1 font-bold">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                <span>Struk</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-10 text-slate-400">
                            Tidak ada transaksi penjualan pada rentang filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $transactions->links() }}
        </div>
    </div>

</div>
@endsection
