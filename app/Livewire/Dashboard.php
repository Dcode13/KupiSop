<?php

namespace App\Livewire;

use App\Models\Ingredient;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Kasir hanya melihat ringkasan transaksinya sendiri; admin melihat semua.
        $salesQuery = Transaction::query()
            ->when($user->hasRole('kasir') && ! $user->hasRole('admin'),
                fn ($q) => $q->where('user_id', $user->id));

        $today = (clone $salesQuery)->whereDate('created_at', today());

        $stats = [
            'today_sales' => (clone $today)->sum('total'),
            'today_count' => (clone $today)->count(),
            'month_sales' => (clone $salesQuery)->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->sum('total'),
            'pending_orders' => Transaction::whereIn('status', [
                Transaction::STATUS_PENDING, Transaction::STATUS_PROCESSING,
            ])->count(),
        ];

        // Produk terlaris (berdasarkan qty terjual, sepanjang waktu)
        $topProducts = TransactionItem::select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Bahan baku yang stoknya menipis
        $lowStock = Ingredient::whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();

        $recent = (clone $salesQuery)->with('user')->latest()->limit(5)->get();

        return view('livewire.dashboard', compact('stats', 'topProducts', 'lowStock', 'recent'));
    }
}
