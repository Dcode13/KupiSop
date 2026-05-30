<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Transaksi')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'from', 'to'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'from', 'to']);
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $transactions = Transaction::with('user')
            ->withCount('items')
            // Kasir hanya melihat transaksinya sendiri
            ->when(! $user->hasRole('admin'), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search, fn ($q) => $q->where('invoice_number', 'like', "%{$this->search}%"))
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->latest()
            ->paginate(15);

        return view('livewire.transactions.index', compact('transactions'));
    }
}
