<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Promotion;
use Carbon\Carbon;

class PosController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Find current open shift for this user
        $activeShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        // Get fast categories and initial popular products
        $categories = Category::withCount('products')->get();
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->limit(50)
            ->get();

        $activePromotions = Promotion::active()->get();

        return view('pos.index', compact('user', 'activeShift', 'categories', 'products', 'activePromotions'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $categoryId = $request->get('category_id');

        $products = Product::with('category')
            ->where('is_active', true)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%")
                        ->orWhere('sku', 'like', "%{$query}%");
                });
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->limit(30)
            ->get();

        return response()->json($products);
    }

    public function openShift(Request $request)
    {
        $request->validate([
            'starting_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        // Check if there's already an open shift
        $existing = Shift::where('user_id', $user->id)->where('status', 'open')->first();
        if ($existing) {
            return back()->with('info', 'Shift Anda sudah dalam status aktif.');
        }

        Shift::create([
            'user_id' => $user->id,
            'starting_cash' => $request->starting_cash,
            'ending_cash_expected' => $request->starting_cash,
            'opened_at' => Carbon::now(),
            'status' => 'open',
            'notes' => $request->notes,
        ]);

        return redirect()->route('pos.index')->with('success', 'Shift kasir berhasil dibuka dengan kas awal Rp ' . number_format($request->starting_cash, 0, ',', '.'));
    }

    public function closeShift(Request $request)
    {
        $request->validate([
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $shift = Shift::where('user_id', $user->id)->where('status', 'open')->first();

        if (!$shift) {
            return back()->withErrors(['shift' => 'Tidak ditemukan shift aktif untuk ditutup.']);
        }

        // Calculate total cash transactions during this shift
        $cashSales = Transaction::where('shift_id', $shift->id)
            ->where('payment_method', 'cash')
            ->where('status', 'completed')
            ->sum('total_amount');

        // Uang kas yang seharusnya ada di laci = Kas Awal + Total Penjualan Tunai
        $expectedCash = $shift->starting_cash + $cashSales;
        $actualCash = $request->actual_cash;
        
        // PERBAIKAN: Selisih kas fisik vs sistem
        $difference = $actualCash - $expectedCash;

        $shift->update([
            'ending_cash_expected' => $expectedCash,
            'ending_cash_actual' => $actualCash,
            'difference' => $difference,
            'closed_at' => Carbon::now(),
            'status' => 'closed',
            'notes' => $request->notes,
        ]);

        return redirect()->route('pos.index')->with('success', 'Shift kasir telah ditutup. Selisih kas: Rp ' . number_format($difference, 0, ',', '.'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'in:cash,qris,debit'],
            'payment_amount' => ['required', 'numeric', 'min:0'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();
        $shift = Shift::where('user_id', $user->id)->where('status', 'open')->first();

        // FIX: Tolak transaksi jika shift kasir belum dibuka. Sebelumnya tidak ada pengecekan,
        // sehingga pembayaran tetap bisa diproses dengan shift_id = null.
        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift kasir belum dibuka. Buka shift terlebih dahulu sebelum melakukan pembayaran.',
            ], 422);
        }

        // FIX: Early `return` di dalam closure DB::transaction() tetap men-COMMIT transaksi,
        // sehingga stok barang sebelumnya sudah terpotong padahal checkout gagal.
        // Sekarang kita lempar exception agar seluruh perubahan di-ROLLBACK.
        try {
            return DB::transaction(function () use ($request, $user, $shift) {
                $subtotal = 0;
                $itemsToCreate = [];

                foreach ($request->items as $cartItem) {
                    $product = Product::lockForUpdate()->find($cartItem['id']);

                    if ($product->stock < $cartItem['qty']) {
                        // FIX: throw (rollback) bukan return
                        throw new \DomainException("Stok produk {$product->name} tidak mencukupi (Tersisa: {$product->stock})");
                    }

                    $itemSubtotal = $product->sell_price * $cartItem['qty'];
                    $itemDiscount = 0; // individual item promo if applicable
                    $subtotal += $itemSubtotal;

                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'qty' => $cartItem['qty'],
                        'unit_price' => $product->sell_price,
                        'cost_price' => $product->buy_price,
                        'discount_amount' => $itemDiscount,
                        'subtotal' => $itemSubtotal,
                    ];

                    // Deduct stock
                    $product->decrement('stock', $cartItem['qty']);
                }

                // Calculate discounts from promotions
                $discountAmount = 0;
                $activePromo = Promotion::active()
                    ->where('min_spend', '<=', $subtotal)
                    ->where('type', 'fixed')
                    ->latest('discount_value')
                    ->first();

                if ($activePromo) {
                    $discountAmount = min($subtotal, $activePromo->discount_value);
                }

                // Tax calculation: PPN 11%
                $taxableAmount = $subtotal - $discountAmount;
                $taxAmount = round($taxableAmount * 0.11, 2);
                $totalAmount = $taxableAmount + $taxAmount;

                if ($request->payment_method === 'cash') {
                    if ($request->payment_amount < $totalAmount) {
                        // FIX: throw (rollback) bukan return, supaya stok yang sudah dipotong dikembalikan
                        throw new \DomainException('Nominal pembayaran tunai kurang dari total belanja.');
                    }
                    $changeAmount = $request->payment_amount - $totalAmount;
                } else {
                    $changeAmount = 0;
                }

                // Generate invoice number: INV-YYMMDD-XXXX
                $datePrefix = date('ymd');
                $latestTrans = Transaction::whereDate('created_at', Carbon::today())->count() + 1;
                $invoiceNumber = 'INV-' . $datePrefix . '-' . sprintf('%04d', $latestTrans);

                $transaction = Transaction::create([
                    'invoice_number' => $invoiceNumber,
                    'cashier_id' => $user->id,
                    'shift_id' => $shift->id, // FIX: shift pasti ada (sudah divalidasi di atas)
                    'customer_name' => $request->customer_name ?: 'Pelanggan Umum',
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'payment_method' => $request->payment_method,
                    'payment_amount' => $request->payment_amount,
                    'change_amount' => $changeAmount,
                    'status' => 'completed',
                    'reference_number' => $request->reference_number,
                ]);

                foreach ($itemsToCreate as $item) {
                    $transaction->items()->create($item);
                }

                // Update kas yang seharusnya ada di laci jika transaksi TUNAI.
                // Uang yang benar-benar masuk ke laci = total belanja (uang diterima - kembalian).
                if ($request->payment_method === 'cash') {
                    $shift->increment('ending_cash_expected', $totalAmount);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil!',
                    'transaction' => $transaction->load(['items', 'cashier']),
                    // FIX: kirim kas terbaru ke frontend agar tampilan "Kas Saat Ini" ikut ter-update
                    'expected_cash' => (float) $shift->fresh()->ending_cash_expected,
                ]);
            });
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt($id)
    {
        $transaction = Transaction::with(['items', 'cashier'])->findOrFail($id);
        return view('pos.receipt', compact('transaction'));
    }
}