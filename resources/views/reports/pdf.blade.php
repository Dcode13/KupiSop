<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #1c1917; }
        h1 { font-size: 18px; margin: 0; }
        .muted { color: #78716c; }
        .header { border-bottom: 2px solid #b45309; padding-bottom: 8px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e7e5e4; text-align: left; }
        th { background: #f5f5f4; }
        .right { text-align: right; }
        .center { text-align: center; }
        .cards td { border: none; padding: 4px 8px; }
        .card { background: #fafaf9; border: 1px solid #e7e5e4; border-radius: 6px; padding: 8px; }
        .card .label { color: #78716c; font-size: 10px; }
        .card .value { font-size: 14px; font-weight: bold; }
    </style>
</head>

<body>
    <div class="header">
        <h1>☕ {{ config('app.name', 'CodeCoffee') }}</h1>
        <div class="muted">Laporan Penjualan · {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</div>
        <div class="muted">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table class="cards">
        <tr>
            <td width="33%"><div class="card"><div class="label">Total Pendapatan</div><div class="value">{{ rupiah($summary['revenue']) }}</div></div></td>
            <td width="33%"><div class="card"><div class="label">Jumlah Transaksi</div><div class="value">{{ $summary['count'] }}</div></div></td>
            <td width="33%"><div class="card"><div class="label">Item Terjual</div><div class="value">{{ (int) $summary['items'] }}</div></div></td>
        </tr>
    </table>

    <h3>Produk Terjual</h3>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th class="center">Qty</th>
                <th class="right">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $p)
                <tr>
                    <td>{{ $p->product_name }}</td>
                    <td class="center">{{ $p->qty }}</td>
                    <td class="right">{{ rupiah($p->revenue) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="center muted">Tidak ada data pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
