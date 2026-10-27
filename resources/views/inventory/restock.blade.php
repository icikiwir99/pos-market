@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="restockApp()">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="truck" class="w-7 h-7 text-blue-600"></i>
                PENERIMAAN BARANG MASUK (RESTOCK)
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Catat faktur penerimaan pasokan barang dari supplier, update stok fisik, dan catat tanggal kedaluwarsa batch</p>
        </div>

        <button @click="showForm = !showForm" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span x-text="showForm ? 'Sembunyikan Form' : 'Input Penerimaan Baru'"></span>
        </button>
    </div>

    <!-- Restock Input Form Section -->
    <div x-show="showForm" x-cloak class="bg-white rounded-2xl border border-blue-200 shadow-md p-6 mb-8 transition-all">
        <div class="border-b border-slate-200 pb-3 mb-4 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-blue-900 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="file-plus" class="w-4 h-4 text-blue-600"></i>
                FORMULIR SURAT JALAN / FAKTUR PENERIMAAN BARANG
            </h2>
            <span class="text-xs text-slate-500 font-medium">Petugas: <strong>{{ auth()->user()->name }}</strong></span>
        </div>

        <form action="{{ route('inventory.restock.store') }}" method="POST" class="space-y-5">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Supplier -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Supplier / Distributor</label>
                    <select name="supplier_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Invoice Number -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Faktur / No. PO</label>
                    <input type="text" name="invoice_number" required placeholder="Contoh: PO-IDM-202610-09"
                           class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <!-- Purchase Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Penerimaan</label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
            </div>

            <!-- Items Table Dynamic -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Rincian Barang yang Diterima</span>
                    <button type="button" @click="addItem()" class="px-2.5 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs font-bold flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Baris</span>
                    </button>
                </div>

                <div class="p-3 space-y-2">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-center bg-slate-50/60 p-2.5 rounded-lg border border-slate-200 text-xs">
                            <!-- Product Selection -->
                            <div class="col-span-12 sm:col-span-4">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase sm:hidden">Produk</label>
                                <select :name="'items['+index+'][product_id]'" x-model="item.product_id" required
                                        @change="updateProductPrice(index)"
                                        class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" data-price="{{ $prod->buy_price }}">{{ $prod->name }} ({{ $prod->unit }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Qty -->
                            <div class="col-span-4 sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase sm:hidden">Qty Masuk</label>
                                <input type="number" :name="'items['+index+'][qty]'" x-model.number="item.qty" required min="1" placeholder="Qty"
                                       class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold text-center">
                            </div>

                            <!-- Cost Price -->
                            <div class="col-span-4 sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase sm:hidden">Harga Beli (Rp)</label>
                                <input type="number" :name="'items['+index+'][cost_price]'" x-model.number="item.cost_price" required min="0" step="100" placeholder="Harga Beli"
                                       class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold">
                            </div>

                            <!-- Expiry Date -->
                            <div class="col-span-3 sm:col-span-3">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase sm:hidden">Tgl Expired</label>
                                <input type="date" :name="'items['+index+'][expiry_date]'" x-model="item.expiry_date"
                                       class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                            </div>

                            <!-- Remove Button -->
                            <div class="col-span-1 text-center">
                                <button type="button" @click="removeItem(index)" class="p-1 text-rose-500 hover:text-rose-700" title="Hapus Baris">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Total Restock Calculation -->
                <div class="bg-slate-50 px-4 py-3 border-t border-slate-200 flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-600 uppercase">Estimasi Total Pembelian:</span>
                    <span class="text-base font-black font-mono text-blue-700" x-text="formatRupiah(calculateTotal())"></span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Catatan kondisi barang / nomor armada pengiriman..."
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-600 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="showForm = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md">
                    Simpan & Tambah Stok Gudang
                </button>
            </div>
        </form>
    </div>

    <!-- Purchases History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Riwayat Dokumen Penerimaan Barang (PO)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Faktur PO</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Tanggal Masuk</th>
                        <th class="py-3 px-4">Petugas Gudang</th>
                        <th class="py-3 px-4 text-center">Jumlah Item</th>
                        <th class="py-3 px-4 text-right">Total Tagihan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $po)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $po->invoice_number }}</td>
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $po->supplier->name }}</td>
                        <td class="py-3 px-4 font-mono text-slate-600">{{ $po->purchase_date->format('d M Y') }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $po->user->name }}</td>
                        <td class="py-3 px-4 text-center font-mono font-bold">{{ $po->items->count() }} macam</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-blue-700">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full">
                                {{ $po->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">
                            Belum ada riwayat penerimaan barang masuk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $purchases->links() }}
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function restockApp() {
    return {
        showForm: false,
        items: [
            { product_id: '', qty: 10, cost_price: 0, expiry_date: '' }
        ],

        addItem() {
            this.items.push({ product_id: '', qty: 10, cost_price: 0, expiry_date: '' });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        updateProductPrice(index) {
            // Find selected option
            const selectEl = document.querySelectorAll("select[name^='items']")[index];
            if (selectEl) {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                if (price) {
                    this.items[index].cost_price = parseFloat(price);
                }
            }
        },

        calculateTotal() {
            return this.items.reduce((acc, it) => acc + ((it.cost_price || 0) * (it.qty || 0)), 0);
        },

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);
        }
    };
}
</script>
@endpush
