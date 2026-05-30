<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #000; margin: 0; }
        .center { text-align: center; }
        .muted { color: #555; font-size: 10px; }
        h1 { font-size: 14px; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .right { text-align: right; }
        .dashed { border-top: 1px dashed #999; margin: 6px 0; height: 0; }
        .total td { font-weight: bold; font-size: 13px; }
    </style>
</head>

<body>
    <div class="center">
        <h1>{{ config('app.name', 'Coffee Shop') }}</h1>
        <div class="muted">Struk Pembayaran</div>
    </div>
    <div class="dashed"></div>

    <table>
        <tr><td class="muted">No. Invoice</td><td class="right">{{ $transaction->invoice_number }}</td></tr>
        <tr><td class="muted">Tanggal</td><td class="right">{{ $transaction->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td class="muted">Kasir</td><td class="right">{{ $transaction->user->name ?? '-' }}</td></tr>
        @if ($transaction->customer_name)
            <tr><td class="muted">Pelanggan</td><td class="right">{{ $transaction->customer_name }}</td></tr>
        @endif
    </table>
    <div class="dashed"></div>

    <table>
        @foreach ($transaction->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="right">{{ rupiah($item->subtotal) }}</td>
            </tr>
            <tr>
                <td class="muted" colspan="2">{{ $item->quantity }} x {{ rupiah($item->price) }}</td>
            </tr>
        @endforeach
    </table>
    <div class="dashed"></div>

    <table>
        <tr class="total"><td>TOTAL</td><td class="right">{{ rupiah($transaction->total) }}</td></tr>
        <tr><td class="muted">Bayar ({{ strtoupper($transaction->payment_method) }})</td><td class="right">{{ rupiah($transaction->paid) }}</td></tr>
        <tr><td class="muted">Kembalian</td><td class="right">{{ rupiah($transaction->change) }}</td></tr>
    </table>
    <div class="dashed"></div>

    <div class="center muted">Terima kasih atas kunjungan Anda!</div>
</body>

</html>
