<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Shift;
use App\Models\Promotion;
use App\Models\Category;
use Carbon\Carbon;

class ManagerController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Today metrics
        $todaySales = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $todayCount = Transaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        // Month metrics
        $monthSales = Transaction::where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Gross Profit Estimation today
        $todayItems = TransactionItem::whereHas('transaction', function ($q) use ($today) {
            $q->whereDate('created_at', $today)->where('status', 'completed');
        })->get();

        $todayProfit = $todayItems->sum(function ($item) {
            return ($item->unit_price - $item->cost_price) * $item->qty;
        });

        // 7-day Trend Data for Chart.js
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('d M');
            $sales = Transaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            $chartData[] = (float) $sales;
        }

        // Top 10 Best Sellers
        $topProducts = TransactionItem::select('product_id', 'product_name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Cashier Performance today
        $cashierPerformances = Transaction::with('cashier')
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->select('cashier_id', DB::raw('COUNT(*) as total_trans'), DB::raw('SUM(total_amount) as total_omset'))
            ->groupBy('cashier_id')
            ->get();

        // Low stock count
        $lowStockCount = Product::lowStock()->count();

        return view('manager.dashboard', compact(
            'todaySales',
            'todayCount',
            'monthSales',
            'todayProfit',
            'chartLabels',
            'chartData',
            'topProducts',
            'cashierPerformances',
            'lowStockCount'
        ));
    }

    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(7)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $cashierId = $request->get('cashier_id');
        $paymentMethod = $request->get('payment_method');

        $query = Transaction::with(['cashier', 'items'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', 'completed')
            ->when($cashierId, function ($q) use ($cashierId) {
                $q->where('cashier_id', $cashierId);
            })
            ->when($paymentMethod, function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            });

        // Summary metrics
        $totalGross = (clone $query)->sum('subtotal');
        $totalDiscount = (clone $query)->sum('discount_amount');
        $totalTax = (clone $query)->sum('tax_amount');
        $totalNet = (clone $query)->sum('total_amount');

        // Profit
        $transactions = $query->latest()->paginate(20)->withQueryString();

        $allCashiers = User::where('role', 'kasir')->get();

        return view('manager.reports.sales', compact(
            'transactions',
            'startDate',
            'endDate',
            'cashierId',
            'paymentMethod',
            'totalGross',
            'totalDiscount',
            'totalTax',
            'totalNet',
            'allCashiers'
        ));
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(7)->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());

        $transactions = Transaction::with(['cashier', 'items'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', 'completed')
            ->latest()
            ->get();

        $fileName = 'laporan-penjualan-pos-indomaret-' . date('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'No. Invoice',
                'Tanggal & Waktu',
                'Kasir',
                'Pelanggan',
                'Subtotal (Rp)',
                'Diskon (Rp)',
                'PPN 11% (Rp)',
                'Total Bersih (Rp)',
                'Metode Bayar',
                'Jumlah Item',
            ]);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->invoice_number,
                    $t->created_at->format('Y-m-d H:i:s'),
                    $t->cashier ? $t->cashier->name : '-',
                    $t->customer_name,
                    $t->subtotal,
                    $t->discount_amount,
                    $t->tax_amount,
                    $t->total_amount,
                    strtoupper($t->payment_method),
                    $t->items->sum('qty'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function shiftReports()
    {
        $shifts = Shift::with(['user', 'transactions'])
            ->latest('opened_at')
            ->paginate(15);

        return view('manager.reports.shifts', compact('shifts'));
    }

    public function stockReports()
    {
        $totalStockValueBuy = Product::all()->sum(function ($p) {
            return $p->stock * $p->buy_price;
        });

        $totalStockValueSell = Product::all()->sum(function ($p) {
            return $p->stock * $p->sell_price;
        });

        $products = Product::with('category')->orderBy('stock', 'asc')->paginate(20);

        return view('manager.reports.stock', compact('products', 'totalStockValueBuy', 'totalStockValueSell'));
    }

    public function users()
    {
        $users = User::latest()->paginate(15);
        return view('manager.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:manager,gudang,kasir'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('manager.users')->with('success', 'Karyawan baru berhasil ditambahkan.');
    }

    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('manager.users')->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    public function promotions()
    {
        $promotions = Promotion::with('product')->latest()->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('manager.promotions', compact('promotions', 'products'));
    }

    public function storePromotion(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:1'],
            'min_spend' => ['required', 'numeric', 'min:0'],
            'product_id' => ['nullable', 'exists:products,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $validated['is_active'] = true;
        Promotion::create($validated);

        return redirect()->route('manager.promotions')->with('success', 'Promo / Diskon baru berhasil dipublikasikan.');
    }
}
