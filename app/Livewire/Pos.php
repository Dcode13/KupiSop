<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\OrderService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kasir (POS)')]
class Pos extends Component
{
    public string $search = '';
    public string $category = '';

    /**
     * Keranjang: array dikunci oleh product_id.
     * [id => ['id','name','price','quantity','subtotal']]
     */
    public array $cart = [];

    public string $customerName = '';
    public string $paymentMethod = 'cash';
    public $paid = '';

    #[Computed]
    public function products()
    {
        return Product::with('category')
            ->where('is_active', true)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return \App\Models\Category::orderBy('name')->get();
    }

    public function getTotalProperty(): float
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function getChangeProperty(): float
    {
        return max(0, (float) ($this->paid ?: 0) - $this->total);
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (! $product || ! $product->is_active) {
            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => 1,
                'subtotal' => 0,
            ];
        }

        $this->recalculate($productId);
    }

    public function increment(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->recalculate($productId);
        }
    }

    public function decrement(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']--;
            if ($this->cart[$productId]['quantity'] <= 0) {
                unset($this->cart[$productId]);
            } else {
                $this->recalculate($productId);
            }
        }
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function clearCart(): void
    {
        $this->reset(['cart', 'customerName', 'paid']);
        $this->paymentMethod = 'cash';
    }

    private function recalculate(int $productId): void
    {
        $item = $this->cart[$productId];
        $this->cart[$productId]['subtotal'] = $item['price'] * $item['quantity'];
    }

    public function checkout(OrderService $orders)
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Keranjang masih kosong.', type: 'error');

            return;
        }

        $total = $this->total;
        $paid = (float) ($this->paid ?: 0);

        // Untuk tunai, uang dibayar harus >= total
        if ($this->paymentMethod === 'cash' && $paid < $total) {
            throw ValidationException::withMessages([
                'paid' => 'Jumlah bayar kurang dari total belanja.',
            ]);
        }

        // QRIS dianggap dibayar pas
        if ($this->paymentMethod === 'qris') {
            $paid = $total;
        }

        try {
            // OrderService menyimpan transaksi + mengurangi stok dalam satu DB::transaction
            $transaction = $orders->place($this->cart, [
                'user_id' => auth()->id(),
                'customer_name' => $this->customerName ?: null,
                'paid' => $paid,
                'change' => $paid - $total,
                'payment_method' => $this->paymentMethod,
                'payment_status' => Transaction::PAY_PAID, // dibayar langsung di kasir
                'paid_at' => now(),
                'status' => Transaction::STATUS_PENDING,
            ]);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('notify', message: $message, type: 'error');

            return;
        }

        $this->clearCart();
        $this->dispatch('notify', message: "Transaksi {$transaction->invoice_number} berhasil disimpan.");

        // Arahkan ke struk untuk dicetak
        return $this->redirect(route('transactions.receipt', $transaction), navigate: false);
    }

    public function render()
    {
        return view('livewire.pos');
    }
}
