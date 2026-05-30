<?php

namespace Tests\Feature;

use App\Livewire\Menu;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_menu_page_is_public(): void
    {
        $this->get('/menu')->assertOk()->assertSee('Menu');
    }

    public function test_home_redirects_guest_to_menu(): void
    {
        $this->get('/')->assertRedirect('/menu');
    }

    public function test_counter_order_creates_unpaid_transaction_and_reduces_stock(): void
    {
        $product = Product::where('name', 'Cappuccino')->firstOrFail(); // Biji Kopi 18
        $bijiKopi = Ingredient::where('name', 'Biji Kopi')->firstOrFail();
        $kopiBefore = (float) $bijiKopi->stock;

        Livewire::test(Menu::class)
            ->call('addToCart', $product->id)
            ->set('paymentChannel', 'counter')
            ->set('customerName', 'Budi')
            ->set('note', 'Meja 5')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $trx = Transaction::latest()->first();
        $this->assertNull($trx->user_id);
        $this->assertEquals('cash', $trx->payment_method);
        $this->assertEquals(Transaction::PAY_UNPAID, $trx->payment_status);
        $this->assertEquals($kopiBefore - 18, (float) $bijiKopi->fresh()->stock);
    }

    public function test_online_order_starts_unpaid_awaiting_method_selection(): void
    {
        $product = Product::where('name', 'Espresso')->firstOrFail();

        Livewire::test(Menu::class)
            ->call('addToCart', $product->id)
            ->set('paymentChannel', 'online')
            ->set('customerName', 'Sari')
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertSet('placedOrderId', fn ($id) => ! is_null($id));

        $trx = Transaction::latest()->first();
        $this->assertEquals('online', $trx->payment_method);
        $this->assertEquals(Transaction::PAY_UNPAID, $trx->payment_status);
        $this->assertNull($trx->payment_details);
    }

    public function test_selecting_a_method_triggers_core_api_charge(): void
    {
        // Mock agar tidak memanggil Midtrans sungguhan
        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('charge')
                ->once()
                ->withArgs(fn ($transaction, $method) => $method === 'qris')
                ->andReturn(['method' => 'qris', 'qr_url' => 'https://example/qr']);
        });

        $product = Product::where('name', 'Espresso')->firstOrFail();

        Livewire::test(Menu::class)
            ->call('addToCart', $product->id)
            ->set('paymentChannel', 'online')
            ->set('customerName', 'Sari')
            ->call('placeOrder')
            ->call('selectPayment', 'qris')
            ->assertHasNoErrors();
    }

    public function test_order_requires_customer_name(): void
    {
        $product = Product::where('name', 'Espresso')->firstOrFail();

        Livewire::test(Menu::class)
            ->call('addToCart', $product->id)
            ->set('paymentChannel', 'counter')
            ->call('placeOrder')
            ->assertHasErrors('customerName');

        $this->assertEquals(0, Transaction::count());
    }

    public function test_settlement_notification_matches_by_payment_ref_and_marks_paid(): void
    {
        $trx = Transaction::create([
            'invoice_number' => 'INV-TEST-0001',
            'customer_name' => 'Sari',
            'total' => 25000,
            'paid' => 0,
            'change' => 0,
            'payment_method' => 'qris',
            'payment_status' => Transaction::PAY_PENDING,
            'payment_ref' => 'INV-TEST-0001-AB12C',
            'status' => Transaction::STATUS_PENDING,
        ]);

        app(MidtransService::class)->applyStatus([
            'order_id' => 'INV-TEST-0001-AB12C', // order_id Midtrans = payment_ref
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ]);

        $trx->refresh();
        $this->assertTrue($trx->isPaid());
        $this->assertEquals(25000, (float) $trx->paid);
        $this->assertNotNull($trx->paid_at);
    }
}
