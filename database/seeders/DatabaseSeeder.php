<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Shift;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $manager = User::create([
            'name' => 'Budi Santoso (Manager)',
            'username' => 'manager',
            'email' => 'manager@indomaret.local',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $gudang = User::create([
            'name' => 'Doni Saputra (Gudang)',
            'username' => 'gudang',
            'email' => 'gudang@indomaret.local',
            'password' => Hash::make('password'),
            'role' => 'gudang',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        $kasir1 = User::create([
            'name' => 'Siti Aminah (Kasir 1)',
            'username' => 'kasir',
            'email' => 'kasir@indomaret.local',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'phone' => '081234567892',
            'is_active' => true,
        ]);

        $kasir2 = User::create([
            'name' => 'Rian Hidayat (Kasir 2)',
            'username' => 'kasir2',
            'email' => 'kasir2@indomaret.local',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'phone' => '081234567893',
            'is_active' => true,
        ]);

        // 2. Categories
        $categoriesData = [
            ['name' => 'Minuman', 'slug' => 'minuman', 'icon' => 'coffee', 'description' => 'Air mineral, teh, kopi, jus, dan minuman bersoda'],
            ['name' => 'Makanan Ringan & Snack', 'slug' => 'makanan-ringan', 'icon' => 'cookie', 'description' => 'Keripik, biskuit, cokelat, dan wafer'],
            ['name' => 'Mie & Makanan Instan', 'slug' => 'mie-instan', 'icon' => 'bowl', 'description' => 'Mie instan, bubur cepat saji, sarden'],
            ['name' => 'Kebutuhan Dapur & Sembako', 'slug' => 'kebutuhan-dapur', 'icon' => 'shopping-cart', 'description' => 'Minyak goreng, beras, gula, bumbu dapur'],
            ['name' => 'Perawatan Diri & Tubuh', 'slug' => 'perawatan-diri', 'icon' => 'sparkles', 'description' => 'Sabun mandi, sampo, pasta gigi, sabun cuci muka'],
            ['name' => 'Pembersih & Rumah Tangga', 'slug' => 'pembersih-rumah', 'icon' => 'home', 'description' => 'Deterjen, pembersih lantai, pewangi pakaian, tissue'],
            ['name' => 'Roti & Siap Santap', 'slug' => 'roti-siap-santap', 'icon' => 'sandwich', 'description' => 'Roti fresh, onigiri, sosis siap makan'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::create($cat);
        }

        // 3. Suppliers
        $suppliersData = [
            [
                'name' => 'PT Indomarco Prismatama DC Jakarta',
                'contact_person' => 'Agus Purnomo',
                'phone' => '021-52994400',
                'email' => 'dc.jakarta@indomaret.co.id',
                'address' => 'Jl. Ancol Barat I No. 9-10, Ancol, Pademangan, Jakarta Utara',
            ],
            [
                'name' => 'PT Indofood CBP Sukses Makmur Tbk',
                'contact_person' => 'Hendra Setiawan',
                'phone' => '021-57958822',
                'email' => 'sales@icbp.indofood.co.id',
                'address' => 'Sudirman Plaza, Indofood Tower Lt. 23, Jl. Jend. Sudirman, Jakarta',
            ],
            [
                'name' => 'PT Unilever Indonesia Tbk',
                'contact_person' => 'Dewi Lestari',
                'phone' => '021-80827000',
                'email' => 'distribusi@unilever.com',
                'address' => 'Grha Unilever, Green Office Park Kav 3, BSD City, Tangerang',
            ],
            [
                'name' => 'PT Mayora Indah Tbk',
                'contact_person' => 'Bambang Tri',
                'phone' => '021-80637000',
                'email' => 'order@mayora.co.id',
                'address' => 'Gedung Mayora, Jl. Tomang Raya 21-23, Jakarta Barat',
            ],
            [
                'name' => 'PT Wings Surya',
                'contact_person' => 'Rudy Hartono',
                'phone' => '031-5324567',
                'email' => 'sales@wingscorp.com',
                'address' => 'Jl. Kalisosok Kidul No. 2, Surabaya',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $sup) {
            $suppliers[] = Supplier::create($sup);
        }

        // 4. Products (Realistic Indomaret goods with authentic EAN barcodes)
        $productsData = [
            // Minuman
            [
                'category_id' => $categories['minuman']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8992761001015',
                'sku' => 'MNM-001',
                'name' => 'Indowowok Air Mineral Botol 600ml',
                'unit' => 'botol',
                'buy_price' => 2400,
                'sell_price' => 3500,
                'stock' => 96,
                'min_stock' => 20,
            ],
            [
                'category_id' => $categories['minuman']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8993077110156',
                'sku' => 'MNM-002',
                'name' => 'Teh Botol Sosro Original Pet 450ml',
                'unit' => 'botol',
                'buy_price' => 4500,
                'sell_price' => 6000,
                'stock' => 48,
                'min_stock' => 15,
            ],
            [
                'category_id' => $categories['minuman']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8992753112217',
                'sku' => 'MNM-003',
                'name' => 'Ultra Milk Susu UHT Cokelat 250ml',
                'unit' => 'kotak',
                'buy_price' => 5200,
                'sell_price' => 7000,
                'stock' => 60,
                'min_stock' => 15,
            ],
            [
                'category_id' => $categories['minuman']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8992819001011',
                'sku' => 'MNM-004',
                'name' => 'Pocari Sweat Isotonik Botol 500ml',
                'unit' => 'botol',
                'buy_price' => 6500,
                'sell_price' => 8500,
                'stock' => 40,
                'min_stock' => 10,
            ],
            [
                'category_id' => $categories['minuman']->id,
                'supplier_id' => $suppliers[3]->id,
                'barcode' => '8991002100234',
                'sku' => 'MNM-005',
                'name' => 'Kopi Kapal Api Spesial Mix 10x24g',
                'unit' => 'renceng',
                'buy_price' => 12500,
                'sell_price' => 15500,
                'stock' => 30,
                'min_stock' => 8,
            ],

            // Mie & Makanan Instan
            [
                'category_id' => $categories['mie-instan']->id,
                'supplier_id' => $suppliers[1]->id,
                'barcode' => '8998866200227',
                'sku' => 'MIE-001',
                'name' => 'Indomie Mi Goreng Spesial 85g',
                'unit' => 'bungkus',
                'buy_price' => 2600,
                'sell_price' => 3300,
                'stock' => 150,
                'min_stock' => 40,
            ],
            [
                'category_id' => $categories['mie-instan']->id,
                'supplier_id' => $suppliers[1]->id,
                'barcode' => '8998866200333',
                'sku' => 'MIE-002',
                'name' => 'Indomie Kuah Rasa Ayam Bawang 69g',
                'unit' => 'bungkus',
                'buy_price' => 2500,
                'sell_price' => 3200,
                'stock' => 120,
                'min_stock' => 30,
            ],
            [
                'category_id' => $categories['mie-instan']->id,
                'supplier_id' => $suppliers[4]->id,
                'barcode' => '8992388114013',
                'sku' => 'MIE-003',
                'name' => 'Mie Sedaap Goreng Kriuk 90g',
                'unit' => 'bungkus',
                'buy_price' => 2550,
                'sell_price' => 3200,
                'stock' => 110,
                'min_stock' => 30,
            ],
            [
                'category_id' => $categories['mie-instan']->id,
                'supplier_id' => $suppliers[1]->id,
                'barcode' => '8998866200999',
                'sku' => 'MIE-004',
                'name' => 'Pop Mie Instant Cup Ayam 75g',
                'unit' => 'cup',
                'buy_price' => 4600,
                'sell_price' => 5800,
                'stock' => 55,
                'min_stock' => 15,
            ],

            // Makanan Ringan
            [
                'category_id' => $categories['makanan-ringan']->id,
                'supplier_id' => $suppliers[1]->id,
                'barcode' => '8992775210014',
                'sku' => 'SNK-001',
                'name' => 'Chitato Sapi Panggang 68g',
                'unit' => 'bungkus',
                'buy_price' => 9200,
                'sell_price' => 12000,
                'stock' => 45,
                'min_stock' => 12,
            ],
            [
                'category_id' => $categories['makanan-ringan']->id,
                'supplier_id' => $suppliers[1]->id,
                'barcode' => '8992775230029',
                'sku' => 'SNK-002',
                'name' => 'Qtela Keripik Singkong Balado 60g',
                'unit' => 'bungkus',
                'buy_price' => 6000,
                'sell_price' => 8200,
                'stock' => 35,
                'min_stock' => 10,
            ],
            [
                'category_id' => $categories['makanan-ringan']->id,
                'supplier_id' => $suppliers[3]->id,
                'barcode' => '8998838110011',
                'sku' => 'SNK-003',
                'name' => 'Beng-Beng Wafer Cokelat 3x25g',
                'unit' => 'pack',
                'buy_price' => 7000,
                'sell_price' => 9500,
                'stock' => 50,
                'min_stock' => 15,
            ],
            [
                'category_id' => $categories['makanan-ringan']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8991001100122',
                'sku' => 'SNK-004',
                'name' => 'SilverQueen Cashew Milk Chocolate 58g',
                'unit' => 'pcs',
                'buy_price' => 13500,
                'sell_price' => 17500,
                'stock' => 4, // Trigger Low Stock!
                'min_stock' => 10,
            ],
            [
                'category_id' => $categories['makanan-ringan']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8992760123456',
                'sku' => 'SNK-005',
                'name' => 'Oreo Biscuit Vanilla Cream 119.6g',
                'unit' => 'roll',
                'buy_price' => 7800,
                'sell_price' => 10500,
                'stock' => 40,
                'min_stock' => 12,
            ],

            // Sembako & Dapur
            [
                'category_id' => $categories['kebutuhan-dapur']->id,
                'supplier_id' => $suppliers[1]->id,
                'barcode' => '8992745100018',
                'sku' => 'SMK-001',
                'name' => 'Minyak Goreng Bimoli Refill 2 Liter',
                'unit' => 'pouch',
                'buy_price' => 33000,
                'sell_price' => 38500,
                'stock' => 45,
                'min_stock' => 15,
            ],
            [
                'category_id' => $categories['kebutuhan-dapur']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8997011234567',
                'sku' => 'SMK-002',
                'name' => 'Gulaku Gula Pasir Premium Kuning 1kg',
                'unit' => 'kg',
                'buy_price' => 15200,
                'sell_price' => 17500,
                'stock' => 60,
                'min_stock' => 15,
            ],
            [
                'category_id' => $categories['kebutuhan-dapur']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8992761009998',
                'sku' => 'SMK-003',
                'name' => 'Indowowok Beras Pulen Super Pandan Wangi 5kg',
                'unit' => 'karung',
                'buy_price' => 68000,
                'sell_price' => 76500,
                'stock' => 20,
                'min_stock' => 5,
            ],
            [
                'category_id' => $categories['kebutuhan-dapur']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8992761008882',
                'sku' => 'SMK-004',
                'name' => 'Telur Ayam Negeri Indowowok Isi 10 Butir',
                'unit' => 'pack',
                'buy_price' => 24000,
                'sell_price' => 28000,
                'stock' => 3, // Low stock trigger
                'min_stock' => 8,
            ],

            // Perawatan Tubuh
            [
                'category_id' => $categories['perawatan-diri']->id,
                'supplier_id' => $suppliers[2]->id,
                'barcode' => '8999999054321',
                'sku' => 'PRW-001',
                'name' => 'Lifebuoy Sabun Cair Total 10 Refill 450ml',
                'unit' => 'pouch',
                'buy_price' => 21000,
                'sell_price' => 26500,
                'stock' => 30,
                'min_stock' => 10,
            ],
            [
                'category_id' => $categories['perawatan-diri']->id,
                'supplier_id' => $suppliers[2]->id,
                'barcode' => '8999999123456',
                'sku' => 'PRW-002',
                'name' => 'Pepsodent Pasta Gigi Pencegah Gigi Berlubang 190g',
                'unit' => 'kotak',
                'buy_price' => 13500,
                'sell_price' => 17000,
                'stock' => 45,
                'min_stock' => 12,
            ],
            [
                'category_id' => $categories['perawatan-diri']->id,
                'supplier_id' => $suppliers[2]->id,
                'barcode' => '8999999567890',
                'sku' => 'PRW-003',
                'name' => 'Sunsilk Sampo Black Shine Botol 170ml',
                'unit' => 'botol',
                'buy_price' => 22000,
                'sell_price' => 27500,
                'stock' => 25,
                'min_stock' => 8,
            ],

            // Pembersih Rumah
            [
                'category_id' => $categories['pembersih-rumah']->id,
                'supplier_id' => $suppliers[2]->id,
                'barcode' => '8999999234567',
                'sku' => 'HSE-001',
                'name' => 'Rinso Molto Deterjen Bubuk Rose Fresh 770g',
                'unit' => 'bungkus',
                'buy_price' => 23000,
                'sell_price' => 28500,
                'stock' => 35,
                'min_stock' => 10,
            ],
            [
                'category_id' => $categories['pembersih-rumah']->id,
                'supplier_id' => $suppliers[2]->id,
                'barcode' => '8999999345678',
                'sku' => 'HSE-002',
                'name' => 'Sunlight Sabun Cuci Piring Jeruk Nipis Refill 700ml',
                'unit' => 'pouch',
                'buy_price' => 14000,
                'sell_price' => 18000,
                'stock' => 40,
                'min_stock' => 12,
            ],
            [
                'category_id' => $categories['pembersih-rumah']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8992761005556',
                'sku' => 'HSE-003',
                'name' => 'Indowowok Facial Tissue Non Perfumed 220s',
                'unit' => 'pack',
                'buy_price' => 8500,
                'sell_price' => 11500,
                'stock' => 70,
                'min_stock' => 20,
            ],

            // Roti Siap Santap
            [
                'category_id' => $categories['roti-siap-santap']->id,
                'supplier_id' => $suppliers[0]->id,
                'barcode' => '8992761007779',
                'sku' => 'RTI-001',
                'name' => 'Mr. Bread Roti Manis Kasur Cokelat Keju',
                'unit' => 'bungkus',
                'buy_price' => 10500,
                'sell_price' => 13500,
                'stock' => 15,
                'min_stock' => 5,
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $prod) {
            $p = Product::create($prod);
            $createdProducts[] = $p;

            // Batch & Expiry Date
            ProductBatch::create([
                'product_id' => $p->id,
                'batch_number' => 'BCH-' . date('Ym') . '-' . rand(100, 999),
                'expiry_date' => Carbon::now()->addMonths(rand(3, 18))->format('Y-m-d'),
                'stock' => $p->stock,
            ]);
        }

        // Add 1 batch that expires in 5 days for expiry warning test
        ProductBatch::create([
            'product_id' => $createdProducts[0]->id, // Air Mineral
            'batch_number' => 'BCH-EXP-NEAR',
            'expiry_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'stock' => 10,
        ]);

        // 5. Promotions
        Promotion::create([
            'name' => 'Promo Diskon Gajian (Min. Belanja 100rb Potongan 10rb)',
            'type' => 'fixed',
            'discount_value' => 10000,
            'min_spend' => 100000,
            'product_id' => null,
            'start_date' => Carbon::now()->subDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(25)->toDateString(),
            'is_active' => true,
        ]);

        Promotion::create([
            'name' => 'Diskon Khusus Indomie Goreng 10%',
            'type' => 'percentage',
            'discount_value' => 10, // 10%
            'min_spend' => 0,
            'product_id' => $createdProducts[5]->id, // Indomie Goreng
            'start_date' => Carbon::now()->subDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(15)->toDateString(),
            'is_active' => true,
        ]);

        // 6. Cashier Shift Sample
        $shift = Shift::create([
            'user_id' => $kasir1->id,
            'starting_cash' => 200000,
            'ending_cash_expected' => 200000,
            'opened_at' => Carbon::now()->startOfDay()->addHours(7),
            'status' => 'open',
            'notes' => 'Shift Pagi Kasir 1',
        ]);

        // 7. Seed Some Realistic Transactions for Dashboard Analytics
        $paymentMethods = ['cash', 'cash', 'qris', 'debit', 'cash'];
        $baseDate = Carbon::now()->subDays(3);

        $shiftTotalCash = 200000;

        for ($day = 0; $day <= 3; $day++) {
            $transCount = ($day === 3) ? 8 : 12; // 8 today, 12 on past days
            $currentDate = $baseDate->copy()->addDays($day);

            for ($t = 1; $t <= $transCount; $t++) {
                $invNumber = 'INV-' . $currentDate->format('ymd') . '-' . sprintf('%04d', ($day * 20) + $t);
                $pMethod = $paymentMethods[array_rand($paymentMethods)];
                
                // pick 2 to 4 random products
                $itemCount = rand(2, 4);
                $pickedProducts = array_rand($createdProducts, $itemCount);
                
                $subtotal = 0;
                $itemsData = [];

                foreach ((array)$pickedProducts as $pIdx) {
                    $itemProduct = $createdProducts[$pIdx];
                    $qty = rand(1, 3);
                    $lineTotal = $itemProduct->sell_price * $qty;
                    $subtotal += $lineTotal;

                    $itemsData[] = [
                        'product_id' => $itemProduct->id,
                        'product_name' => $itemProduct->name,
                        'qty' => $qty,
                        'unit_price' => $itemProduct->sell_price,
                        'cost_price' => $itemProduct->buy_price,
                        'discount_amount' => 0,
                        'subtotal' => $lineTotal,
                    ];
                }

                $tax = round($subtotal * 0.11, 2); // PPN 11%
                $discount = ($subtotal > 80000) ? 5000 : 0;
                $total = $subtotal - $discount + $tax;

                if ($pMethod === 'cash') {
                    $paymentAmount = ceil($total / 10000) * 10000;
                    if ($paymentAmount < $total) $paymentAmount += 10000;
                    $changeAmount = $paymentAmount - $total;
                    if ($day === 3) {
                        $shiftTotalCash += $total;
                    }
                } else {
                    $paymentAmount = $total;
                    $changeAmount = 0;
                }

                $transaction = Transaction::create([
                    'invoice_number' => $invNumber,
                    'cashier_id' => ($t % 2 === 0) ? $kasir2->id : $kasir1->id,
                    'shift_id' => ($day === 3) ? $shift->id : null,
                    'customer_name' => 'Pelanggan ' . (($t % 3 === 0) ? 'Member' : 'Umum'),
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'tax_amount' => $tax,
                    'total_amount' => $total,
                    'payment_method' => $pMethod,
                    'payment_amount' => $paymentAmount,
                    'change_amount' => $changeAmount,
                    'reference_number' => ($pMethod !== 'cash') ? 'REF-' . rand(10000000, 99999999) : null,
                    'status' => 'completed',
                    'created_at' => $currentDate->copy()->addHours(8 + rand(1, 10))->addMinutes(rand(0, 59)),
                    'updated_at' => $currentDate->copy()->addHours(8 + rand(1, 10))->addMinutes(rand(0, 59)),
                ]);

                foreach ($itemsData as $item) {
                    $transaction->items()->create($item);
                }
            }
        }

        $shift->update([
            'ending_cash_expected' => $shiftTotalCash,
        ]);
    }
}
