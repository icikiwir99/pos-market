@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ createModal: false }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="tag" class="w-7 h-7 text-purple-600"></i>
                MANAJEMEN PROMOSI & DISKON RETAIL
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Atur skema diskon belanja otomatis di layar kasir berdasarkan nominal belanja atau kategori</p>
        </div>

        <button @click="createModal = true" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Buat Promo Baru</span>
        </button>
    </div>

    <!-- Promotions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Program Promo</th>
                        <th class="py-3 px-4 text-center">Tipe Diskon</th>
                        <th class="py-3 px-4 text-right">Besaran Diskon</th>
                        <th class="py-3 px-4 text-right">Syarat Min. Belanja</th>
                        <th class="py-3 px-4">Target Khusus</th>
                        <th class="py-3 px-4">Periode Aktif</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($promotions as $promo)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $promo->name }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($promo->type === 'fixed')
                                <span class="bg-blue-100 text-blue-800 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full">Potongan Tunai</span>
                            @else
                                <span class="bg-purple-100 text-purple-800 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full">Persentase (%)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-rose-600">
                            @if($promo->type === 'fixed')
                                Rp {{ number_format($promo->discount_value, 0, ',', '.') }}
                            @else
                                {{ $promo->discount_value }}%
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-700">
                            Rp {{ number_format($promo->min_spend, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $promo->product ? $promo->product->name : 'Semua Produk (Global Belanja)' }}
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-600">
                            {{ $promo->start_date->format('d/m/Y') }} s/d {{ $promo->end_date->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($promo->is_active && $promo->end_date >= now()->toDateString())
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full">
                                    Aktif
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-500 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full">
                                    Kedaluwarsa
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">
                            Belum ada promosi retail yang aktif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL BUAT PROMO -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden" @click.outside="createModal = false">
            <div class="bg-purple-600 text-white px-5 py-3.5 flex items-center justify-between">
                <h3 class="text-sm font-extrabold flex items-center gap-2">
                    <i data-lucide="tag" class="w-4 h-4 text-white"></i>
                    BUAT PROMOSI / DISKON BARU
                </h3>
                <button @click="createModal = false" class="text-white/80 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form action="{{ route('manager.promotions.store') }}" method="POST" class="p-5 space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nama Program Promo</label>
                    <input type="text" name="name" required placeholder="Contoh: Diskon Gajian 10rb"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-purple-600 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tipe Diskon</label>
                        <select name="type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-600 focus:outline-none">
                            <option value="fixed">Nominal Tetap (Rp)</option>
                            <option value="percentage">Persentase (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nilai Diskon</label>
                        <input type="number" name="discount_value" required min="1" placeholder="10000"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Minimal Total Belanja (Rp)</label>
                    <input type="number" name="min_spend" value="0" min="0" step="1000"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-purple-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Produk Khusus (Opsional)</label>
                    <select name="product_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-600 focus:outline-none">
                        <option value="">Semua Produk (Diskon Keranjang)</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tanggal Berakhir</label>
                        <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider shadow-md">
                        Aktifkan Promosi Retail
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
