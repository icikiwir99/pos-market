<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->invoice_number }} - Indowowok</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.3;
            margin: 0;
            padding: 10px;
            background: #f8fafc;
        }
        .receipt-card {
            max-width: 80mm;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            border: 1px solid #cbd5e1;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .dashed { border-top: 1px dashed #64748b; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; }
        .btn-print {
            display: block;
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            background: #00509d;
            color: #fff;
            font-weight: bold;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .btn-print { display: none; }
            .receipt-card { border: none; padding: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="receipt-card">
        <button class="btn-print" onclick="window.print()">CETAK STRUK (THERMAL)</button>

        <div class="text-center">
            <h2 style="margin: 0; font-size: 16px; font-weight: 900; color: #00509d;">INDOMARET</h2>
            <div>PT INDOMARCO PRISMATAMA</div>
            <div style="font-size: 10px;">TOKO 084 - JL. RAYA UTAMA NO. 45</div>
            <div style="font-size: 10px;">NPWP: 01.324.567.8-091.000</div>
        </div>

        <div class="dashed"></div>

        <div style="font-size: 11px;">
            <div class="row">
                <span>No. Bon:</span>
                <span class="font-bold">{{ $transaction->invoice_number }}</span>
            </div>
            <div class="row">
                <span>Waktu:</span>
                <span>{{ $transaction->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="row">
                <span>Kasir:</span>
                <span>{{ $transaction->cashier ? $transaction->cashier->name : '-' }}</span>
            </div>
            <div class="row">
                <span>Pelanggan:</span>
                <span>{{ $transaction->customer_name }}</span>
            </div>
        </div>

        <div class="dashed"></div>

        <div>
            @foreach($transaction->items as $item)
                <div style="margin-bottom: 4px;">
                    <div class="font-bold">{{ $item->product_name }}</div>
                    <div class="row" style="font-size: 11px;">
                        <span>{{ $item->qty }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="dashed"></div>

        <div style="font-size: 11px;">
            <div class="row">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($transaction->discount_amount > 0)
                <div class="row" style="color: #dc2626;">
                    <span>Diskon Promo:</span>
                    <span>- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="row">
                <span>PPN 11%:</span>
                <span>Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</span>
            </div>
            <div class="row font-bold" style="font-size: 13px; margin-top: 4px;">
                <span>TOTAL AKHIR:</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="row" style="margin-top: 4px;">
                <span>Metode:</span>
                <span style="text-transform: uppercase;">{{ $transaction->payment_method }}</span>
            </div>
            <div class="row">
                <span>Bayar:</span>
                <span>Rp {{ number_format($transaction->payment_amount, 0, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Kembali:</span>
                <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="dashed"></div>

        <div class="text-center" style="font-size: 10px; color: #475569;">
            <div>TERIMA KASIH ATAS KUNJUNGAN ANDA</div>
            <div>LAYANAN KONSUMEN: 1500-280</div>
            <div>WWW.INDOWOWOK.CO.ID</div>
        </div>
    </div>
</body>
</html>
