<?php

namespace App\Livewire\Orders;

use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pesanan')]
class Index extends Component
{
    #[Url]
    public string $status = 'pending';

    public function updateStatus(Transaction $transaction, string $status): void
    {
        $allowed = [
            Transaction::STATUS_PENDING,
            Transaction::STATUS_PROCESSING,
            Transaction::STATUS_DONE,
        ];

        if (! in_array($status, $allowed, true)) {
            return;
        }

        $transaction->update(['status' => $status]);
        $this->dispatch('notify', message: "Pesanan {$transaction->invoice_number} → {$transaction->statusLabel()}.");
    }

    public function render()
    {
        $orders = Transaction::with('items', 'user')
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->orderByRaw("FIELD(status, 'diproses', 'pending', 'selesai')")
            ->latest()
            ->paginate(12);

        $counts = [
            'pending' => Transaction::where('status', Transaction::STATUS_PENDING)->count(),
            'diproses' => Transaction::where('status', Transaction::STATUS_PROCESSING)->count(),
            'selesai' => Transaction::where('status', Transaction::STATUS_DONE)->count(),
        ];

        return view('livewire.orders.index', compact('orders', 'counts'));
    }
}
