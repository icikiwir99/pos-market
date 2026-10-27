<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockAdjustment;
use Carbon\Carbon;

class InventoryController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $lowStockProducts = Product::lowStock()->count();
        $outOfStockProducts = Product::where('stock', '<=', 0)->count();

        // Expiring in next 30 days
        $expiringBatches = ProductBatch::with('product')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('stock', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        $recentRestocks = Purchase::with(['supplier', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        $recentAdjustments = StockAdjustment::with(['product', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        return view('inventory.index', compact(
            'totalProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'expiringBatches',
            'recentRestocks',
            'recentAdjustments'
        ));
    }

    public function products(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $stockFilter = $request->get('stock_filter');

        $categories = Category::all();

        $products = Product::with(['category', 'supplier', 'batches'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($stockFilter === 'low', function ($q) {
                $q->whereColumn('stock', '<=', 'min_stock');
            })
            ->when($stockFilter === 'empty', function ($q) {
                $q->where('stock', '<=', 0);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('inventory.products', compact('products', 'categories', 'search', 'categoryId', 'stockFilter'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'barcode' => ['required', 'string', 'unique:products,barcode'],
            'sku' => ['required', 'string', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::create($validated);

        if ($product->stock > 0) {
            ProductBatch::create([
                'product_id' => $product->id,
                'batch_number' => 'BCH-INIT-' . date('Ymd'),
                'expiry_date' => Carbon::now()->addYear()->toDateString(),
                'stock' => $product->stock,
            ]);
        }

        return redirect()->route('inventory.products')->with('success', "Produk {$product->name} berhasil ditambahkan ke inventaris.");
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'barcode' => ['required', 'string', 'unique:products,barcode,' . $product->id],
            'sku' => ['required', 'string', 'unique:products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $product->update($validated);

        return redirect()->route('inventory.products')->with('success', "Data produk {$product->name} berhasil diperbarui.");
    }

    public function restockIndex()
    {
        $purchases = Purchase::with(['supplier', 'user', 'items.product'])
            ->latest()
            ->paginate(15);
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('inventory.restock', compact('purchases', 'suppliers', 'products'));
    }

    public function storeRestock(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'invoice_number' => ['required', 'string', 'unique:purchases,invoice_number'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.cost_price' => ['required', 'numeric', 'min:0'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $totalAmount += ($item['cost_price'] * $item['qty']);
            }

            $purchase = Purchase::create([
                'invoice_number' => $request->invoice_number,
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
                'status' => 'received',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['cost_price'] * $item['qty'];

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'cost_price' => $item['cost_price'],
                    'subtotal' => $subtotal,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // Update product stock and buy price
                $product = Product::find($item['product_id']);
                $product->increment('stock', $item['qty']);
                $product->update(['buy_price' => $item['cost_price']]);

                // Add batch if expiry date is given
                if (!empty($item['expiry_date'])) {
                    ProductBatch::create([
                        'product_id' => $product->id,
                        'batch_number' => 'BCH-PO-' . $purchase->invoice_number,
                        'expiry_date' => $item['expiry_date'],
                        'stock' => $item['qty'],
                    ]);
                }
            }
        });

        return redirect()->route('inventory.restock')->with('success', 'Penerimaan barang masuk (restock) berhasil dicatat dan stok gudang telah diperbarui.');
    }

    public function adjustmentsIndex()
    {
        $adjustments = StockAdjustment::with(['product', 'user'])
            ->latest()
            ->paginate(15);
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('inventory.adjustments', compact('adjustments', 'products'));
    }

    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:in,out,correction'],
            'qty' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'in:rusak,bocor,kadaluarsa,selisih_opname,lainnya'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->type === 'out' && $product->stock < $request->qty) {
            return back()->withErrors(['qty' => 'Pengurangan stok melebihi stok yang tersedia saat ini (' . $product->stock . ').']);
        }

        DB::transaction(function () use ($request, $product) {
            StockAdjustment::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => $request->type,
                'qty' => $request->qty,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            if ($request->type === 'in') {
                $product->increment('stock', $request->qty);
            } elseif ($request->type === 'out') {
                $product->decrement('stock', $request->qty);
            } elseif ($request->type === 'correction') {
                // For correction, set the stock to the exact value
                $product->update(['stock' => $request->qty]);
            }
        });

        return redirect()->route('inventory.adjustments')->with('success', "Penyesuaian stok produk {$product->name} berhasil disimpan.");
    }
}
