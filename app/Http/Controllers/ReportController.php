<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Ambil rentang tanggal dari request (default: bulan berjalan).
     *
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon}
     */
    protected function range(Request $request): array
    {
        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->toDateString()))->endOfDay();

        return [$from, $to];
    }

    public function csv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $transactions = Transaction::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $filename = 'laporan-penjualan-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($transactions) {
            $out = fopen('php://output', 'w');
            // Header kolom
            fputcsv($out, ['Invoice', 'Tanggal', 'Kasir', 'Pelanggan', 'Metode', 'Status', 'Total', 'Bayar', 'Kembalian']);

            foreach ($transactions as $trx) {
                fputcsv($out, [
                    $trx->invoice_number,
                    $trx->created_at->format('Y-m-d H:i'),
                    $trx->user->name ?? '-',
                    $trx->customer_name ?? '-',
                    $trx->payment_method,
                    $trx->status,
                    $trx->total,
                    $trx->paid,
                    $trx->change,
                ]);
            }

            // Baris total
            fputcsv($out, []);
            fputcsv($out, ['', '', '', '', '', 'TOTAL', $transactions->sum('total'), '', '']);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function pdf(Request $request)
    {
        [$from, $to] = $this->range($request);

        $base = Transaction::whereBetween('created_at', [$from, $to]);

        $summary = [
            'revenue' => (clone $base)->sum('total'),
            'count' => (clone $base)->count(),
            'items' => TransactionItem::whereHas('transaction',
                fn ($q) => $q->whereBetween('created_at', [$from, $to]))->sum('quantity'),
        ];

        $products = TransactionItem::select('product_name',
                DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('transaction', fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->get();

        $pdf = Pdf::loadView('reports.pdf', [
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'products' => $products,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-penjualan-'.$from->format('Ymd').'-'.$to->format('Ymd').'.pdf');
    }
}
