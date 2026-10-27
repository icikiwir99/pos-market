<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PosIndomaretTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('POINT OF SALE');
        $response->assertSee('1-Klik Akses Cepat');
    }

    public function test_kasir_login_redirects_to_pos(): void
    {
        $kasir = User::where('role', 'kasir')->first();

        $response = $this->post('/login', [
            'login' => $kasir->username,
            'password' => 'password',
        ]);

        $response->assertRedirect('/pos');
        $this->assertAuthenticatedAs($kasir);
    }

    public function test_gudang_login_redirects_to_inventory(): void
    {
        $gudang = User::where('role', 'gudang')->first();

        $response = $this->post('/login', [
            'login' => $gudang->username,
            'password' => 'password',
        ]);

        $response->assertRedirect('/inventory');
        $this->assertAuthenticatedAs($gudang);
    }

    public function test_manager_login_redirects_to_dashboard(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->post('/login', [
            'login' => $manager->username,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($manager);
    }

    public function test_role_based_access_control_prevents_unauthorized_access(): void
    {
        $kasir = User::where('role', 'kasir')->first();

        // Kasir cannot access manager dashboard
        $response = $this->actingAs($kasir)->get('/dashboard');
        $response->assertStatus(403);

        // Kasir cannot access manager user management
        $response = $this->actingAs($kasir)->get('/manager/users');
        $response->assertStatus(403);
    }

    public function test_pos_search_products_api(): void
    {
        $kasir = User::where('role', 'kasir')->first();

        $response = $this->actingAs($kasir)->get('/pos/search?q=Indomie');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'barcode' => '8998866200227'
        ]);
    }

    public function test_pos_checkout_flow_and_stock_reduction(): void
    {
        $kasir = User::where('role', 'kasir')->first();
        $product = Product::where('barcode', '8992761001015')->first(); // Air mineral
        $initialStock = $product->stock;

        $payload = [
            'items' => [
                ['id' => $product->id, 'qty' => 2]
            ],
            'payment_method' => 'cash',
            'payment_amount' => 50000,
            'customer_name' => 'Budi Pelanggan Test',
        ];

        $response = $this->actingAs($kasir)->postJson('/pos/checkout', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Verify stock reduced
        $this->assertEquals($initialStock - 2, $product->fresh()->stock);

        // Verify transaction recorded
        $this->assertDatabaseHas('transactions', [
            'customer_name' => 'Budi Pelanggan Test',
            'cashier_id' => $kasir->id,
            'payment_method' => 'cash',
        ]);
    }

    public function test_inventory_restock_flow(): void
    {
        $gudang = User::where('role', 'gudang')->first();
        $supplier = Supplier::first();
        $product = Product::first();
        $initialStock = $product->stock;

        $payload = [
            'supplier_id' => $supplier->id,
            'invoice_number' => 'PO-TEST-' . rand(1000, 9999),
            'purchase_date' => date('Y-m-d'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 20,
                    'cost_price' => $product->buy_price,
                    'expiry_date' => Carbon::now()->addMonths(6)->toDateString(),
                ]
            ]
        ];

        $response = $this->actingAs($gudang)->post('/inventory/restock', $payload);
        $response->assertRedirect('/inventory/restock');

        // Check product stock increased
        $this->assertEquals($initialStock + 20, $product->fresh()->stock);
    }

    public function test_inventory_stock_adjustment(): void
    {
        $gudang = User::where('role', 'gudang')->first();
        $product = Product::first();
        $initialStock = $product->stock;

        $payload = [
            'product_id' => $product->id,
            'type' => 'out',
            'qty' => 3,
            'reason' => 'rusak',
            'notes' => 'Kemasan pecah saat bongkar muat'
        ];

        $response = $this->actingAs($gudang)->post('/inventory/adjustments', $payload);
        $response->assertRedirect('/inventory/adjustments');

        // Check stock reduced by 3
        $this->assertEquals($initialStock - 3, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $product->id,
            'reason' => 'rusak',
            'qty' => 3
        ]);
    }

    public function test_manager_dashboard_access_and_metrics(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('DASHBOARD EKSEKUTIF MANAGER');
        $response->assertSee('Omset Penjualan Hari Ini');
    }
}
