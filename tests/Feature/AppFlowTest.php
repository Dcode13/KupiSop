<?php

namespace Tests\Feature;

use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Pos;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // roles, users, sample products & ingredients
    }

    private function admin(): User
    {
        return User::where('email', 'admin@coffee.test')->first();
    }

    private function kasir(): User
    {
        return User::where('email', 'kasir@coffee.test')->first();
    }

    private function barista(): User
    {
        return User::where('email', 'barista@coffee.test')->first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_access_all_main_pages(): void
    {
        $this->actingAs($this->admin());

        foreach (['/dashboard', '/pos', '/products', '/categories', '/ingredients',
            '/transactions', '/orders', '/users', '/reports'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_role_protection_blocks_unauthorized_pages(): void
    {
        // Barista tidak boleh ke halaman admin (produk)
        $this->actingAs($this->barista())->get('/products')->assertForbidden();
        // Kasir tidak boleh ke manajemen pengguna
        $this->actingAs($this->kasir())->get('/users')->assertForbidden();
        // Barista tidak boleh ke POS
        $this->actingAs($this->barista())->get('/pos')->assertForbidden();
    }

    public function test_pos_checkout_creates_transaction_and_reduces_stock(): void
    {
        $kasir = $this->kasir();
        // Cappuccino: Biji Kopi 18, Susu 150
        $product = Product::where('name', 'Cappuccino')->firstOrFail();
        $bijiKopi = Ingredient::where('name', 'Biji Kopi')->firstOrFail();
        $susu = Ingredient::where('name', 'Susu')->firstOrFail();

        $stockKopiBefore = (float) $bijiKopi->stock;
        $stockSusuBefore = (float) $susu->stock;

        Livewire::actingAs($kasir)
            ->test(Pos::class)
            ->call('addToCart', $product->id)
            ->call('increment', $product->id) // qty = 2
            ->set('paymentMethod', 'cash')
            ->set('paid', 100000)
            ->call('checkout')
            ->assertHasNoErrors();

        // Transaksi tersimpan
        $trx = Transaction::where('user_id', $kasir->id)->latest()->first();
        $this->assertNotNull($trx);
        $this->assertStringStartsWith('INV-', $trx->invoice_number);
        $this->assertEquals(2 * (float) $product->price, (float) $trx->total);
        $this->assertEquals(1, $trx->items()->count());
        $this->assertEquals(2, $trx->items()->first()->quantity);

        // Stok bahan baku berkurang (18 & 150 per item × 2)
        $this->assertEquals($stockKopiBefore - 36, (float) $bijiKopi->fresh()->stock);
        $this->assertEquals($stockSusuBefore - 300, (float) $susu->fresh()->stock);
    }

    public function test_pos_rejects_insufficient_payment(): void
    {
        $kasir = $this->kasir();
        $product = Product::where('name', 'Espresso')->firstOrFail(); // 18000

        Livewire::actingAs($kasir)
            ->test(Pos::class)
            ->call('addToCart', $product->id)
            ->set('paymentMethod', 'cash')
            ->set('paid', 1000) // kurang
            ->call('checkout')
            ->assertHasErrors('paid');

        $this->assertEquals(0, Transaction::count());
    }

    public function test_barista_can_advance_order_status(): void
    {
        // Buat transaksi via kasir dulu
        $kasir = $this->kasir();
        $product = Product::where('name', 'Espresso')->firstOrFail();

        Livewire::actingAs($kasir)->test(Pos::class)
            ->call('addToCart', $product->id)
            ->set('paid', 50000)
            ->call('checkout');

        $trx = Transaction::latest()->first();
        $this->assertEquals(Transaction::STATUS_PENDING, $trx->status);

        Livewire::actingAs($this->barista())
            ->test(OrdersIndex::class)
            ->call('updateStatus', $trx->id, Transaction::STATUS_PROCESSING);

        $this->assertEquals(Transaction::STATUS_PROCESSING, $trx->fresh()->status);
    }
}
