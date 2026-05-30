<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan')]
class Index extends Component
{
    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public function mount(): void
    {
        if ($this->from === '') {
            $this->from = now()->startOfMonth()->toDateString();
        }
        if ($this->to === '') {
            $this->to = now()->toDateString();
        }
    }

    public function setPeriod(string $period): void
    {
        match ($period) {
            'today' => [$this->from = now()->toDateString(), $this->to = now()->toDateString()],
            'week' => [$this->from = now()->startOfWeek()->toDateString(), $this->to = now()->endOfWeek()->toDateString()],
            'month' => [$this->from = now()->startOfMonth()->toDateString(), $this->to = now()->toDateString()],
            default => null,
        };
    }

    public function render()
    {
        $from = Carbon::parse($this->from)->startOfDay();
        $to = Carbon::parse($this->to)->endOfDay();

        $base = Transaction::whereBetween('created_at', [$from, $to]);

        $summary = [
            'revenue' => (clone $base)->sum('total'),
            'count' => (clone $base)->count(),
            'items' => TransactionItem::whereHas('transaction',
                fn ($q) => $q->whereBetween('created_at', [$from, $to]))->sum('quantity'),
        ];
        $summary['avg'] = $summary['count'] > 0 ? $summary['revenue'] / $summary['count'] : 0;

        // Produk terjual
        $products = TransactionItem::select('product_name',
                DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('transaction', fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->get();

        // Penjualan harian
        $daily = (clone $base)
            ->select(DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('livewire.reports.index', compact('summary', 'products', 'daily'));
    }
}
