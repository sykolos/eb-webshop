<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PartnerRegistrationRequest;
use App\Models\Products;
use App\Models\User;
use App\Models\User_shipping;
use App\Models\Item;
use App\Models\product_unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class M10AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_request_is_pending_until_admin_approval(): void
    {
        $this->withoutVite();
        $email = 'partner-'.Str::uuid().'@example.test';

        $this->post(route('register.submit'), [
            'name' => 'Teszt Partner',
            'email' => $email,
            'company_name' => 'Teszt Partner Kft.',
            'vatnumber' => '12345678-1-42',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
        ])->assertRedirect(route('login'));

        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame('pending', $user->account_status);
        $request = PartnerRegistrationRequest::where('user_id', $user->id)->firstOrFail();

        $admin = User::factory()->create(['is_admin' => true]);
        $approval = $this->actingAs($admin)->post(route('adminpanel.partner-requests.update', $request), [
            'status' => 'approved',
        ]);
        $approval->assertRedirect();

        $this->assertDatabaseHas('partner_registration_requests', ['id' => $request->id, 'status' => 'approved']);
        $this->assertSame('active', $user->fresh()->account_status);
        $this->post(route('login.submit'), ['email' => $email, 'password' => 'strong-password'])
            ->assertRedirect('/');
    }

    public function test_pending_partner_cannot_login_before_approval(): void
    {
        $this->withoutVite();
        $email = 'pending-'.Str::uuid().'@example.test';
        $this->post(route('register.submit'), [
            'name' => 'Pending Partner', 'email' => $email, 'company_name' => 'Pending Kft.',
            'password' => 'strong-password', 'password_confirmation' => 'strong-password',
        ]);

        $this->post(route('login.submit'), ['email' => $email, 'password' => 'strong-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_can_return_to_a_local_page_without_open_redirect(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['account_status' => 'active']);

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'password',
            'return_to' => '/cart',
        ])->assertRedirect('/cart');

        $this->post(route('logout'))->assertRedirect();
        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'password',
            'return_to' => 'https://example.test/cart',
        ])->assertRedirect('/');
    }

    public function test_account_addresses_have_one_default_and_are_private(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $legacy = User::factory()->create();
        User_shipping::create([
            'user_id' => $legacy->id, 'receiver' => 'Régi cím', 'phone' => '0633333333',
            'zipcode' => '3333', 'city' => 'Vác', 'address' => 'Régi utca 3.', 'is_default' => false,
        ]);
        $this->actingAs($legacy)->get(route('account.modify'))->assertOk();
        $this->assertDatabaseHas('user_shippings', ['user_id' => $legacy->id, 'is_default' => true]);

        $this->actingAs($owner)->post(route('shipping.store'), [
            'receiver' => 'Első', 'phone' => '0611111111', 'zipcode' => '1111',
            'city' => 'Budapest', 'address' => 'Első utca 1.',
        ])->assertRedirect(route('account.modify'));
        $first = User_shipping::where('user_id', $owner->id)->firstOrFail();

        $this->actingAs($owner)->post(route('shipping.store'), [
            'receiver' => 'Második', 'phone' => '0622222222', 'zipcode' => '2222',
            'city' => 'Budapest', 'address' => 'Második utca 2.',
        ])->assertRedirect(route('account.modify'));
        $second = User_shipping::where('user_id', $owner->id)->where('id', '<>', $first->id)->firstOrFail();

        $this->actingAs($owner)->post(route('shipping.default', $second))->assertRedirect();
        $this->assertSame(1, User_shipping::where('user_id', $owner->id)->where('is_default', true)->count());
        $this->actingAs($other)->post(route('shipping.default', $first))->assertForbidden();
    }

    public function test_reorder_uses_current_product_and_favorites_are_persisted_per_user(): void
    {
        $user = User::factory()->create();
        $unit = product_unit::create(['unit' => 'Doboz', 'quantity' => 1, 'measure' => 'db']);
        $product = Products::create([
            'serial_number' => 'M10-001', 'title' => 'Újrarendelhető termék',
            'description' => 'Teszt', 'price' => 1200, 'category_id' => 1, 'unit_id' => $unit->id,
        ]);
        $order = Order::create([
            'user_id' => $user->id, 'name' => 'Teszt Kft.', 'email' => $user->email,
            'address' => 'Teszt utca 1.', 'total' => 1000, 'status' => 'függőben',
        ]);
        Item::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 900]);

        $this->actingAs($user)->post(route('account.favorite.store', $product->id))->assertRedirect();
        $this->assertDatabaseHas('user_favorites', ['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)->post(route('account.orders.reorder', $order))->assertRedirect(route('cart'));
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(2, (int) CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)->value('quantity'));
    }
}
