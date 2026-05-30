<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Menu & Pesan')]
class Menu extends Component
{
    public string $search = '';
    public string $category = '';

    /** Keranjang: [product_id => ['id','name','price','quantity','subtotal']] */
    public array $cart = [];

    public string $customerName = '';
    public string $note = '';

    /** Metode bayar: 'online' (Midtrans) atau 'counter' (di kasir). */
    public string $paymentChannel = 'online';

    /** ID transaksi setelah pesanan dibuat (untuk layar konfirmasi & lacak status). */
    public ?int $placedOrderId = null;

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

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

    public function getTotalProperty(): float
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function getCountProperty(): int
    {
        return collect($this->cart)->sum('quantity');
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
        $this->dispatch('added-to-cart');
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

    private function recalculate(int $productId): void
    {
        $item = $this->cart[$productId];
        $this->cart[$productId]['subtotal'] = $item['price'] * $item['quantity'];
    }

    public function placeOrder(OrderService $orders)
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'note' => 'nullable|string|max:500',
        ], [
            'customerName.required' => 'Mohon isi nama Anda.',
        ]);

        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Keranjang masih kosong.', type: 'error');

            return;
        }

        $online = $this->paymentChannel === 'online';

        try {
            $transaction = $orders->place($this->cart, [
                'customer_name' => $this->customerName,
                'note' => $this->note ?: null,
                // Untuk online, metode spesifik (qris/gopay/...) ditentukan saat memilih di popup.
                'payment_method' => $online ? 'online' : 'cash',
                'payment_status' => Transaction::PAY_UNPAID,
                'status' => Transaction::STATUS_PENDING,
            ]);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('notify', message: $message, type: 'error');

            return;
        }

        // Tampilkan layar konfirmasi (popup pembayaran untuk order online)
        $this->placedOrderId = $transaction->id;
        $this->reset(['cart', 'note']);
        $this->dispatch('order-placed'); // tutup drawer keranjang
    }

    /**
     * Pelanggan memilih metode pembayaran → charge via Core API.
     */
    public function selectPayment(string $method): void
    {
        $transaction = $this->placedOrderId ? Transaction::find($this->placedOrderId) : null;
        if (! $transaction || $transaction->isPaid()) {
            return;
        }

        try {
            app(MidtransService::class)->charge($transaction, $method);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', message: 'Gagal memproses pembayaran. Silakan coba metode lain.', type: 'error');
        }
    }

    /**
     * Kembali ke pemilihan metode (batalkan instruksi pembayaran aktif).
     */
    public function changeMethod(): void
    {
        $transaction = $this->placedOrderId ? Transaction::find($this->placedOrderId) : null;
        if ($transaction && ! $transaction->isPaid()) {
            $transaction->update([
                'payment_details' => null,
                'payment_ref' => null,
                'payment_method' => 'online',
            ]);
        }
    }

    /**
     * Pelanggan memilih bayar di kasir (batalkan pembayaran online).
     */
    public function payAtCounter(): void
    {
        $transaction = $this->placedOrderId ? Transaction::find($this->placedOrderId) : null;
        if ($transaction && ! $transaction->isPaid()) {
            $transaction->update([
                'payment_method' => 'cash',
                'payment_status' => Transaction::PAY_UNPAID,
                'payment_details' => null,
                'payment_ref' => null,
            ]);
        }
    }

    /**
     * Sinkronkan status pembayaran dari Midtrans (dipanggil polling popup).
     */
    public function confirmPayment(): void
    {
        $transaction = $this->placedOrderId ? Transaction::find($this->placedOrderId) : null;

        if ($transaction && $transaction->payment_ref && ! $transaction->isPaid()) {
            app(MidtransService::class)->syncStatus($transaction);
        }
    }

    public function newOrder(): void
    {
        $this->reset(['placedOrderId', 'customerName', 'note', 'cart']);
        $this->paymentChannel = 'online';
    }

    public function render()
    {
        $placedOrder = $this->placedOrderId
            ? Transaction::with('items')->find($this->placedOrderId)
            : null;

        return view('livewire.menu', compact('placedOrder'));
    }
}
