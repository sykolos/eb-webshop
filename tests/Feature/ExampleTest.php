<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Products;
use App\Models\product_unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
