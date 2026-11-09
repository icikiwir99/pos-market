<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Promotion;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    // Search produk berdasarkan Barcode atau Nama
    public function searchProduct(Request $request)
    {
        $query = $request->get('query');
        $product = Product::where('barcode', $query)
            ->orWhere('name', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json($product);
    }

    // Hitung logika diskon otomatis
    private function calculateDiscount($productId, $qty, $subtotal)
    {
        $discount = 0;
        $now = now();

        $promo = Promotion::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where(function ($q) use ($productId) {
                $q->where('product_id', $productId)
                  ->orWhereNull('product_id');
            })
            ->first();

        if ($promo) {
            if ($promo->type === 'percentage') {
                $discount = ($subtotal * $promo->value) / 100;
            } elseif ($promo->type === 'fixed') {
                $discount = $promo->value * $qty;
            }
        }

        return $discount;
    }

    // Simpan Transaksi (Checkout)
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'pay_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        // Cek shift aktif kasir
        $activeShift = Shift::where('user_id', auth()->id())
            ->whereNull('end_time')
            ->first();

        return DB::transaction(function () use ($request, $activeShift) {
            $totalAmount = 0;
            $totalDiscount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                // Validasi Stok
                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok produk '{$product->name}' tidak mencukupi. Sisa stok: {$product->stock}");
                }

                $subtotal = $product->selling_price * $item['qty'];
                $discount = $this->calculateDiscount($product->id, $item['qty'], $subtotal);
                $finalSubtotal = $subtotal - $discount;

                $totalAmount += $subtotal;
                $totalDiscount += $discount;

                // Kurangi stok produk
                $product->decrement('stock', $item['qty']);

                $itemsData[] = [
                    'product_id' => $product->id,
                    'price' => $product->selling_price,
                    'qty' => $item['qty'],
                    'discount' => $discount,
                    'subtotal' => $finalSubtotal,
                ];
            }

            $finalAmount = $totalAmount - $totalDiscount;

            if ($request->pay_amount < $finalAmount) {
                throw new \Exception("Jumlah pembayaran kurang. Total yang harus dibayar: Rp " . number_format($finalAmount));
            }

            $changeAmount = $request->pay_amount - $finalAmount;

            // Buat Record Transaksi
            $transaction = Transaction::create([
                'invoice_number' => 'INV-' . date('YmdHis') . '-' . Str::random(4),
                'user_id' => auth()->id(),
                'shift_id' => $activeShift ? $activeShift->id : null,
                'total_amount' => $totalAmount,
                'discount_amount' => $totalDiscount,
                'final_amount' => $finalAmount,
                'pay_amount' => $request->pay_amount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
            ]);

            // Simpan Detail Transaksi
            foreach ($itemsData as $detail) {
                $transaction->items()->create($detail);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil diproses',
                'data' => $transaction->load('items.product'),
            ], 201);
        });
    }
}