@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ showForm: false }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="sliders-horizontal" class="w-7 h-7 text-purple-600"></i>
                PENYESUAIAN STOK (STOCK ADJUSTMENT)
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Koreksi jumlah stok manual untuk barang rusak, bocor, kadaluarsa, atau hasil audit stok opname</p>
        </div>

        <button @click="showForm = !showForm" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span x-text="showForm ? 'Tutup Form' : 'Input Penyesuaian Baru'"></span>
        </button>
    </div>

    <!-- Adjustment Form -->
    <div x-show="showForm" x-cloak class="bg-white rounded-2xl border border-purple-200 shadow-md p-6 mb-8 transition-all">
        <div class="border-b border-slate-200 pb-3 mb-4 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-purple-900 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="clipboard-pen" class="w-4 h-4 text-purple-600"></i>
                FORMULIR PENYESUAIAN STOK FISIK
            </h2>
            <span class="text-xs text-slate-500 font-medium">Petugas: <strong>{{ auth()->user()->name }}</strong></span>
        </div>

        <form action="{{ route('inventory.adjustments.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Product -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Produk</label>
                    <select name="product_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-600 focus:outline-none">
                        <option value="">-- Pilih Produk yang Disesuaikan --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Stok saat ini: {{ $p->stock }} {{ $p->unit }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Penyesuaian</label>
                    <select name="type" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-600 focus:outline-none">
                        <option value="out">Kurang Stok (-) [Rusak/Bocor/Kadaluarsa]</option>
                        <option value="in">Tambah Stok (+) [Ditemukan Fisik Lebih]</option>
                        <option value="correction">Koreksi Langsung (=) [Set Nilai Stok Baru]</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Qty -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jumlah Qty yang Disesuaikan</label>
                    <input type="number" name="qty" required min="1" placeholder="Contoh: 2"
                           class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-purple-600 focus:outline-none">
                </div>

                <!-- Reason -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Penyesuaian</label>
                    <select name="reason" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-600 focus:outline-none">
                        <option value="rusak">Barang Rusak / Kemasan Pecah</option>
                        <option value="bocor">Bocor / Tumpah</option>
                        <option value="kadaluarsa">Kedaluwarsa (Expired)</option>
                        <option value="selisih_opname">Selisih Stok Opname Bulanan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan / Keterangan Tambahan</label>
                <textarea name="notes" rows="2" placeholder="Catatan bukti fisik, berita acara pemusnahan barang, dll..."
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-purple-600 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="showForm = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-md">
                    Simpan Penyesuaian Stok
                </button>
            </div>
        </form>
    </div>

    <!-- Adjustments History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Catatan Riwayat Penyesuaian Stok Barang</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4 text-center">Tipe</th>
                        <th class="py-3 px-4 text-center">Jumlah Qty</th>
                        <th class="py-3 px-4">Alasan</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($adjustments as $adj)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono text-slate-600">{{ $adj->created_at->format('d M Y H:i') }}</td>
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $adj->product->name }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($adj->type === 'in')
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full">Tambah (+)</span>
                            @elseif($adj->type === 'out')
                                <span class="bg-rose-100 text-rose-800 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full">Kurang (-)</span>
                            @else
                                <span class="bg-blue-100 text-blue-800 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full">Koreksi (=)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-slate-900">
                            {{ $adj->qty }} {{ $adj->product->unit }}
                        </td>
                        <td class="py-3 px-4 font-semibold capitalize text-slate-700">
                            <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px]">{{ str_replace('_', ' ', $adj->reason) }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $adj->user->name }}</td>
                        <td class="py-3 px-4 text-slate-500 italic max-w-xs truncate">{{ $adj->notes ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">
                            Belum ada catatan penyesuaian stok.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $adjustments->links() }}
        </div>
    </div>

</div>
@endsection
