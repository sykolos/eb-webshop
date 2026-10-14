<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Products;
use App\Models\product_unit;
use App\Models\User_invoce;
use App\Models\User_shipping;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $this->withoutVite();
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guest_release_routes_and_authentication_guards(): void
    {
        $this->withoutVite();
        foreach (['/contact', '/login'] as $path) {
            $this->get($path)->assertOk();
        }
        foreach (['/account', '/adminpanel'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }

    public function test_discount_index_migration_is_repeatable_in_isolated_tests(): void
    {
        $migration = require database_path('migrations/2026_02_27_140043_add_unique_index_to_group_discounts_table.php');
        $migration->up();
        $migration->down();
        $migration->down();
        $migration->up();
        $migration->up();
        $indexes = \Illuminate\Support\Facades\DB::select("PRAGMA index_list('group_discounts')");
        $matching = array_values(array_filter($indexes, fn ($index) => $index->name === 'group_discounts_user_category_unique'));
        $this->assertCount(1, $matching);
        $this->assertSame(1, (int) $matching[0]->unique);
    }

    public function test_partner_login_reaches_account_but_not_admin_area(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['password' => bcrypt('partner-password')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'partner-password',
        ])->assertRedirect('/');

        $this->get('/account')->assertOk();
        $this->get('/adminpanel')->assertRedirect('/login');
    }

    public function test_admin_login_reaches_admin_area(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create([
            'password' => bcrypt('admin-password'),
            'is_admin' => true,
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'admin-password',
        ])->assertRedirect('/');

        $this->get('/adminpanel')->assertOk();
    }

    public function test_account_order_details_and_pdf_are_limited_to_the_order_owner(): void
    {
        $this->withoutVite();
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::create([
            'user_id' => $owner->id,
            'name' => $owner->name,
            'email' => $owner->email,
            'address' => 'Teszt utca 1.',
            'total' => 1000,
            'status' => 'függőben',
        ]);

        $this->actingAs($otherUser)->get('/account/details/'.$order->id)->assertForbidden();
        $this->actingAs($otherUser)->get('/account/getpdf/'.$order->id)->assertForbidden();
        $this->actingAs($owner)->get('/account/details/'.$order->id)->assertOk();
    }

    public function test_cache_clear_is_not_exposed_as_a_public_route(): void
    {
        $this->get('/clear')->assertNotFound();
    }

    public function test_cart_add_validates_quantity_and_uses_the_product_packaging(): void
    {
        $unit = product_unit::create([
            'unit' => 'Doboz',
            'quantity' => 12,
            'measure' => 'db',
        ]);
        $product = Products::create([
            'serial_number' => 'CART-001',
            'title' => 'Kosár teszttermék',
            'description' => 'Teszt',
            'price' => 1000,
            'category_id' => 1,
            'unit_id' => $unit->id,
        ]);

        $this->post(route('addtocart', [
            'id' => $product->id,
            'q' => '999999',
            'm' => 'tampered',
        ]), ['quantity' => '2'])->assertRedirect();

        $item = session('cart.0');
        $this->assertSame(2, $item['quantity']);
        $this->assertSame(12, $item['q']);
        $this->assertSame('db', $item['m']);
    }

    public function test_cart_add_rejects_invalid_quantities(): void
    {
        $unit = product_unit::create([
            'unit' => 'Darab',
            'quantity' => 1,
            'measure' => 'db',
        ]);
        $product = Products::create([
            'serial_number' => 'CART-002',
            'title' => 'Érvénytelen mennyiség teszttermék',
            'description' => 'Teszt',
            'price' => 1000,
            'category_id' => 1,
            'unit_id' => $unit->id,
        ]);

        foreach (['0', '-1', '1.5', 'not-a-number'] as $quantity) {
            $this->from('/shop')->post(route('addtocart', [
                'id' => $product->id,
                'q' => '1',
                'm' => 'db',
            ]), ['quantity' => $quantity])
                ->assertRedirect('/shop')
                ->assertSessionHasErrors('quantity');
        }

        $this->assertNull(session('cart'));
    }

    public function test_cart_add_returns_not_found_for_an_unknown_product(): void
    {
        $this->post(route('addtocart', [
            'id' => 999999,
            'q' => '1',
            'm' => 'db',
        ]), ['quantity' => 1])->assertNotFound();

        $this->post('/add-to-cart/not-a-product/1/db', ['quantity' => 1])
            ->assertNotFound();
    }

    public function test_cart_quantity_update_only_accepts_single_step_changes(): void
    {
        session(['cart' => [[
            'product' => ['id' => 1],
            'quantity' => 2,
            'q' => 1,
            'm' => 'db',
        ]]]);

        $this->post(route('cart.update'), ['key' => 0, 'change' => 5])
            ->assertSessionHasErrors('change');
        $this->assertSame(2, session('cart.0.quantity'));

        $this->post(route('cart.update'), ['key' => 0, 'change' => 1])
            ->assertNoContent();
        $this->assertSame(3, session('cart.0.quantity'));
    }

    public function test_checkout_rejects_missing_billing_data_without_creating_an_order(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $shipping = User_shipping::create([
            'user_id' => $user->id,
            'address' => 'Teszt utca 1.',
            'city' => 'Budapest',
            'zipcode' => '1111',
            'phone' => '0612345678',
            'receiver' => 'Teszt Elek',
        ]);
        $token = (string) Str::uuid();

        $this->actingAs($user)
            ->from('/checkout')
            ->withSession(['cart' => [[
                'product' => ['id' => 999999],
                'quantity' => 1,
                'q' => 1,
                'm' => 'db',
            ]]])
            ->post(route('validcheckout'), [
                'shipping_address_id' => $shipping->id,
                'payment_method' => 0,
                'checkout_token' => $token,
            ])
            ->assertRedirect('/checkout')
            ->assertSessionHasErrors('billing');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_a_deleted_cart_product_without_creating_an_order(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        User_invoce::create([
            'user_id' => $user->id,
            'company_name' => 'Teszt Kft.',
            'address' => 'Számla utca 1.',
            'city' => 'Budapest',
            'state' => 'Budapest',
            'zipcode' => '1111',
            'country' => 'Magyarország',
            'vatnumber' => '12345678-1-42',
        ]);
        $shipping = User_shipping::create([
            'user_id' => $user->id,
            'address' => 'Teszt utca 1.',
            'city' => 'Budapest',
            'zipcode' => '1111',
            'phone' => '0612345678',
            'receiver' => 'Teszt Elek',
        ]);

        $this->actingAs($user)
            ->from('/checkout')
            ->withSession(['cart' => [[
                'product' => ['id' => 999999],
                'quantity' => 1,
                'q' => 1,
                'm' => 'db',
            ]]])
            ->post(route('validcheckout'), [
                'shipping_address_id' => $shipping->id,
                'payment_method' => 0,
                'checkout_token' => (string) Str::uuid(),
            ])
            ->assertRedirect('/checkout')
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_service_reuses_an_order_for_the_same_submission_token(): void
    {
        $user = User::factory()->create();
        User_invoce::create([
            'user_id' => $user->id,
            'company_name' => 'Teszt Kft.',
            'address' => 'Számla utca 1.',
            'city' => 'Budapest',
            'state' => 'Budapest',
            'zipcode' => '1111',
            'country' => 'Magyarország',
            'vatnumber' => '12345678-1-42',
        ]);
        $shipping = User_shipping::create([
            'user_id' => $user->id,
            'address' => 'Teszt utca 1.',
            'city' => 'Budapest',
            'zipcode' => '1111',
            'phone' => '0612345678',
            'receiver' => 'Teszt Elek',
        ]);
        $unit = product_unit::create([
            'unit' => 'Doboz',
            'quantity' => 6,
            'measure' => 'db',
        ]);
        $product = Products::create([
            'serial_number' => 'CHECKOUT-001',
            'title' => 'Checkout teszttermék',
            'description' => 'Teszt',
            'price' => 1000,
            'category_id' => 1,
            'unit_id' => $unit->id,
        ]);
        $data = [
            'shipping_address_id' => $shipping->id,
            'payment_method' => 0,
            'checkout_token' => (string) Str::uuid(),
        ];
        $cart = [[
            'product' => ['id' => $product->id],
            'quantity' => 2,
            'q' => 999,
            'm' => 'tampered',
        ]];

        $first = app(CheckoutService::class)->createOrder($user, $data, $cart);
        $second = app(CheckoutService::class)->createOrder($user, $data, $cart);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('items', [
            'order_id' => $first->id,
            'product_id' => $product->id,
            'quantity' => 12,
            'unit_price' => 1000,
        ]);
    }

    public function test_admin_order_status_updates_follow_the_allowed_transition_graph(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Teszt Kft.',
            'email' => $admin->email,
            'address' => 'Teszt utca 1.',
            'total' => 1000,
            'status' => 'függőben',
        ]);

        $this->actingAs($admin)
            ->from(route('adminpanel.orders.view', $order->id))
            ->post(route('adminpanel.orders.status.update', $order->id), ['status' => 'feldolgozás'])
            ->assertRedirect(route('adminpanel.orders.view', $order->id));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'feldolgozás']);

        $this->actingAs($admin)
            ->from(route('adminpanel.orders.view', $order->id))
            ->post(route('adminpanel.orders.status.update', $order->id), ['status' => 'függőben'])
            ->assertRedirect(route('adminpanel.orders.view', $order->id))
            ->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'feldolgozás']);

        $this->actingAs($admin)
            ->from(route('adminpanel.orders.view', $order->id))
            ->post(route('adminpanel.orders.status.update', $order->id), ['status' => 'ismeretlen'])
            ->assertRedirect(route('adminpanel.orders.view', $order->id))
            ->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'feldolgozás']);
    }
}
