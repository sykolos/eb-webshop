<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Products;
use App\Models\User;
use App\Models\User_invoce;
use App\Models\User_shipping;
use App\Models\product_unit;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class M09CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_cart_migration_is_idempotent_and_persists_the_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product('M09-MERGE');
        $payload = [[
            'product' => ['id' => $product->id],
            'quantity' => 2,
            'q' => 999,
            'm' => 'tampered',
        ]];

        $service = app(CartService::class);
        $service->migrateSession($user, $payload);
        $service->migrateSession($user, $payload);

        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(2, $cart->items()->firstOrFail()->quantity);
        $this->assertNotNull($cart->session_migration_hash);
    }

    public function test_adding_a_product_merges_quantities_and_uses_database_packaging(): void
    {
        $user = User::factory()->create();
        $product = $this->product('M09-ADD', 6);
        $service = app(CartService::class);

        $service->add($user, $product->id, 2);
        $service->add($user, $product->id, 3);

        $item = Cart::where('user_id', $user->id)->firstOrFail()->items()->firstOrFail();
        $this->assertSame(5, $item->quantity);
        $this->assertSame(6, $item->product->product_unit->quantity);
    }

    public function test_cart_item_cannot_be_changed_by_another_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $cart = Cart::create(['user_id' => $owner->id]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product('M09-OWNER')->id,
            'quantity' => 2,
        ]);

        $this->actingAs($other)
            ->postJson(route('cart.update'), ['key' => $item->id, 'change' => 1])
            ->assertNotFound();

        $this->assertSame(2, $item->fresh()->quantity);
    }

    public function test_checkout_uses_current_price_and_saves_order_snapshots(): void
    {
        $user = User::factory()->create();
        $this->invoice($user);
        $shipping = $this->shipping($user);
        $product = $this->product('M09-CHECKOUT', 4, 1000);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 2]);

        $product->update(['price' => 1250]);
        $order = app(CheckoutService::class)->createOrder($user, [
            'shipping_address_id' => $shipping->id,
            'payment_method' => 0,
            'checkout_token' => (string) Str::uuid(),
        ], $cart);

        $this->assertSame('EBR-'.now()->format('Ymd').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT), $order->displayNumber());
        $this->assertSame(10000, (int) $order->subtotal_net);
        $this->assertDatabaseHas('items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 8,
            'unit_price' => 1250,
            'product_title' => 'M09-CHECKOUT termék',
            'pack_quantity' => 4,
        ]);
    }

    public function test_checkout_controller_clears_the_database_cart_only_after_success(): void
    {
        $this->withoutVite();
        Queue::fake();
        $user = User::factory()->create();
        $this->invoice($user);
        $shipping = $this->shipping($user);
        $product = $this->product('M09-CONTROLLER');
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($user)->post(route('validcheckout'), [
            'shipping_address_id' => $shipping->id,
            'payment_method' => 0,
            'checkout_token' => (string) Str::uuid(),
        ]);

        $response->assertOk()->assertViewIs('pages.orderSuccess');
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('orders', 1);
        Queue::assertPushed(\App\Jobs\SendOrderNotifications::class);
    }

    public function test_duplicate_checkout_token_reuses_the_same_database_order(): void
    {
        $user = User::factory()->create();
        $this->invoice($user);
        $shipping = $this->shipping($user);
        $product = $this->product('M09-IDEMPOTENT');
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1]);
        $data = [
            'shipping_address_id' => $shipping->id,
            'payment_method' => 0,
            'checkout_token' => (string) Str::uuid(),
        ];

        $first = app(CheckoutService::class)->createOrder($user, $data, $cart);
        $second = app(CheckoutService::class)->createOrder($user, $data, $cart);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_unavailable_product_keeps_the_cart_for_correction(): void
    {
        $user = User::factory()->create();
        $this->invoice($user);
        $shipping = $this->shipping($user);
        $product = $this->product('M09-UNAVAILABLE');
        $product->update(['availability_status' => 'out_of_stock']);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1]);

        try {
            app(CheckoutService::class)->createOrder($user, [
                'shipping_address_id' => $shipping->id,
                'payment_method' => 0,
                'checkout_token' => (string) Str::uuid(),
            ], $cart);
            $this->fail('A nem elérhető terméknek validációs hibát kell adnia.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('cart', $exception->errors());
        }

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseCount('orders', 0);
    }

    private function product(string $serial, int $packQuantity = 1, int $price = 1000): Products
    {
        $unit = product_unit::create([
            'unit' => 'Doboz',
            'quantity' => $packQuantity,
            'measure' => 'db',
        ]);

        return Products::create([
            'serial_number' => $serial,
            'title' => $serial.' termék',
            'description' => 'M09 teszttermék',
            'price' => $price,
            'category_id' => 1,
            'unit_id' => $unit->id,
        ]);
    }

    private function invoice(User $user): User_invoce
    {
        return User_invoce::create([
            'user_id' => $user->id,
            'company_name' => 'M09 Teszt Kft.',
            'address' => 'Számla utca 1.',
            'city' => 'Budapest',
            'state' => 'Budapest',
            'zipcode' => '1111',
            'country' => 'Magyarország',
            'vatnumber' => '12345678-1-42',
        ]);
    }

    private function shipping(User $user): User_shipping
    {
        return User_shipping::create([
            'user_id' => $user->id,
            'address' => 'Teszt utca 1.',
            'city' => 'Budapest',
            'zipcode' => '1111',
            'phone' => '0612345678',
            'receiver' => 'Teszt Elek',
        ]);
    }
}
