<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $transaction->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #e7e5e4; padding: 24px; color: #1c1917; }
        .receipt { width: 320px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
        .center { text-align: center; }
        h1 { font-size: 18px; }
        .muted { color: #78716c; font-size: 12px; }
        hr { border: none; border-top: 1px dashed #d6d3d1; margin: 12px 0; }
        table { width: 100%; font-size: 13px; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .row { display: flex; justify-content: space-between; font-size: 13px; margin: 2px 0; }
        .total { font-weight: 700; font-size: 16px; }
        .actions { width: 320px; margin: 16px auto 0; display: flex; gap: 8px; }
        .btn { flex: 1; padding: 10px; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; text-align: center; text-decoration: none; }
        .btn-primary { background: #b45309; color: #fff; }
        .btn-light { background: #fff; color: #44403c; border: 1px solid #d6d3d1; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; width: 100%; }
            .actions { display: none; }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <div class="center">
            <h1>☕ {{ config('app.name', 'Coffee Shop') }}</h1>
            <p class="muted">Struk Pembayaran</p>
        </div>
        <hr>
        <div class="row"><span class="muted">No. Invoice</span><span>{{ $transaction->invoice_number }}</span></div>
        <div class="row"><span class="muted">Tanggal</span><span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span class="muted">Kasir</span><span>{{ $transaction->user->name ?? '-' }}</span></div>
        @if ($transaction->customer_name)
            <div class="row"><span class="muted">Pelanggan</span><span>{{ $transaction->customer_name }}</span></div>
        @endif
        @if ($transaction->note)
            <div class="row"><span class="muted">Catatan</span><span>{{ $transaction->note }}</span></div>
        @endif
        <hr>
        <table>
            @foreach ($transaction->items as $item)
                <tr>
                    <td>{{ $item->product_name }}<br>
                        <span class="muted">{{ $item->quantity }} x {{ rupiah($item->price) }}</span>
                    </td>
                    <td class="right">{{ rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
        </table>
        <hr>
        <div class="row total"><span>TOTAL</span><span>{{ rupiah($transaction->total) }}</span></div>
        <div class="row"><span class="muted">Bayar ({{ strtoupper($transaction->payment_method) }})</span><span>{{ rupiah($transaction->paid) }}</span></div>
        <div class="row"><span class="muted">Kembalian</span><span>{{ rupiah($transaction->change) }}</span></div>
        <hr>
        <p class="center muted">Terima kasih atas kunjungan Anda! 🙏</p>
    </div>

    <div class="actions">
        <a href="{{ route('pos') }}" class="btn btn-light">← Kasir</a>
        <a href="{{ route('transactions.receipt.pdf', $transaction) }}" target="_blank" class="btn btn-light">PDF</a>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak</button>
    </div>
</body>

</html>
