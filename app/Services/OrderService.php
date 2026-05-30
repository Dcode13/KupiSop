<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Simpan satu pesanan/transaksi secara atomik:
     * validasi & kurangi stok bahan baku sesuai resep, lalu buat transaksi + itemnya.
     *
     * @param  array<int, array{id:int,name:string,price:float,quantity:int,subtotal:float}>  $cart
     *         Keranjang dikunci oleh product_id.
     * @param  array<string, mixed>  $attributes  Override kolom transaksi (user_id, customer_name, paid, dst.)
     *
     * @throws ValidationException Bila keranjang kosong atau stok bahan tidak mencukupi.
     */
    public function place(array $cart, array $attributes = []): Transaction
    {
        if (empty($cart)) {
            throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
        }

        $total = collect($cart)->sum('subtotal');

        return DB::transaction(function () use ($cart, $attributes, $total) {
            // Muat produk + resep, kunci baris untuk mencegah race condition.
            $products = Product::with('ingredients')
                ->whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Hitung total kebutuhan bahan baku dari seluruh keranjang.
            $required = [];
            foreach ($cart as $productId => $item) {
                $product = $products->get($productId);
                if (! $product) {
                    continue;
                }
                foreach ($product->ingredients as $ingredient) {
                    $required[$ingredient->id] = ($required[$ingredient->id] ?? 0)
                        + ($ingredient->pivot->quantity * $item['quantity']);
                }
            }

            // Validasi & kurangi stok (dikunci di dalam transaksi).
            if (! empty($required)) {
                $ingredients = Ingredient::whereIn('id', array_keys($required))
                    ->lockForUpdate()->get();

                foreach ($ingredients as $ingredient) {
                    if ($ingredient->stock < $required[$ingredient->id]) {
                        throw ValidationException::withMessages([
                            'cart' => "Stok '{$ingredient->name}' tidak mencukupi.",
                        ]);
                    }
                }
                foreach ($ingredients as $ingredient) {
                    $ingredient->decrement('stock', $required[$ingredient->id]);
                }
            }

            // Buat transaksi.
            $transaction = Transaction::create(array_merge([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'user_id' => null,
                'customer_name' => null,
                'note' => null,
                'total' => $total,
                'paid' => 0,
                'change' => 0,
                'payment_method' => 'cash',
                'status' => Transaction::STATUS_PENDING,
            ], $attributes, ['total' => $total]));

            // Simpan detail item.
            foreach ($cart as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $transaction;
        });
    }
}
