<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    /**
     * Pastikan user boleh melihat struk ini.
     * Admin: semua. Kasir: hanya transaksi miliknya.
     */
    protected function authorizeView(Transaction $transaction): void
    {
        $user = Auth::user();

        abort_unless(
            $user->hasRole('admin') || $transaction->user_id === $user->id,
            403
        );
    }

    public function show(Transaction $transaction)
    {
        $this->authorizeView($transaction);
        $transaction->load('items', 'user');

        return view('receipts.show', compact('transaction'));
    }

    public function pdf(Transaction $transaction)
    {
        $this->authorizeView($transaction);
        $transaction->load('items', 'user');

        $pdf = Pdf::loadView('receipts.pdf', compact('transaction'))
            ->setPaper([0, 0, 226.77, 600], 'portrait'); // ~80mm thermal width

        return $pdf->stream("struk-{$transaction->invoice_number}.pdf");
    }
}
