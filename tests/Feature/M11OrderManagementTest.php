<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Order;
use App\Models\Products;
use App\Models\Special_prices;
use App\Models\User;
use App\Models\User_invoce;
use App\Models\User_shipping;
use App\Models\product_unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class M11OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_list_filters_and_inline_status_change_are_server_side(): void
    {
        $this->withoutVite();
        Queue::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $partner = User::factory()->create(['name' => 'Szűrt Partner', 'account_status' => 'active']);
        User_invoce::create(['user_id' => $partner->id, 'company_name' => 'Szűrt Partner Kft.', 'address' => 'Fő út 1.', 'city' => 'Vác', 'zipcode' => '2600', 'country' => 'Magyarország']);
        $order = Order::create(['user_id' => $partner->id, 'name' => 'Szűrt Partner Kft.', 'email' => $partner->email, 'address' => 'Fő út 1.', 'total' => 1200, 'subtotal_net' => 1200, 'status' => 'függőben']);

        $this->actingAs($admin)->get(route('adminpanel.orders', ['search' => 'Szűrt Partner']))
            ->assertOk()->assertSee($order->displayNumber());

        $this->actingAs($admin)->post(route('adminpanel.orders.status.update', $order), [
            'status' => 'feldolgozás', 'note' => 'Összekészítés elindult',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'feldolgozás', 'status_note' => 'Összekészítés elindult']);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'from_status' => 'függőben', 'to_status' => 'feldolgozás']);
    }

    public function test_bulk_status_change_reports_partial_transition_errors(): void
    {
        $this->withoutVite();
        Queue::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $first = Order::create(['user_id' => $admin->id, 'name' => 'Első', 'email' => $admin->email, 'address' => 'Cím', 'total' => 1, 'status' => 'függőben']);
        $finished = Order::create(['user_id' => $admin->id, 'name' => 'Kész', 'email' => $admin->email, 'address' => 'Cím', 'total' => 1, 'status' => 'kiszállítva']);

        $this->actingAs($admin)->post(route('adminpanel.orders.bulk-status'), [
            'order_ids' => [$first->id, $finished->id], 'status' => 'feldolgozás',
        ])->assertRedirect()->assertSessionHas('bulk_errors');

        $this->assertDatabaseHas('orders', ['id' => $first->id, 'status' => 'feldolgozás']);
        $this->assertDatabaseHas('orders', ['id' => $finished->id, 'status' => 'kiszállítva']);
    }

    public function test_manual_order_uses_partner_pricing_and_is_visible_in_partner_account(): void
    {
        $this->withoutVite();
        Queue::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $partner = User::factory()->create(['account_status' => 'active']);
        User_invoce::create(['user_id' => $partner->id, 'company_name' => 'Partner Kft.', 'address' => 'Számlázási út 2.', 'city' => 'Vác', 'zipcode' => '2600', 'country' => 'Magyarország']);
        $shipping = User_shipping::create(['user_id' => $partner->id, 'receiver' => 'Átvevő', 'phone' => '06201234567', 'zipcode' => '2600', 'city' => 'Vác', 'address' => 'Szállítási út 3.']);
        $unit = product_unit::create(['unit' => 'Doboz', 'quantity' => 2, 'measure' => 'db', 'is_active' => true]);
        $product = Products::create(['serial_number' => 'M11-001', 'title' => 'Partner áras termék', 'description' => 'Teszt', 'price' => 1000, 'category_id' => 1, 'unit_id' => $unit->id, 'is_published' => true, 'is_archived' => false, 'availability_status' => 'in_stock']);
        Special_prices::create(['user_id' => $partner->id, 'product_id' => $product->id, 'price' => 700]);
        $key = (string) Str::uuid();

        $this->actingAs($admin)->post(route('adminpanel.orders.store'), [
            'partner_id' => $partner->id, 'shipping_address_id' => $shipping->id,
            'payment_method' => 0, 'initial_status' => 'függőben', 'idempotency_key' => $key,
            'confirm_order' => '1',
            'lines' => [['product_id' => $product->id, 'quantity' => 3]],
        ])->assertRedirect();

        $order = Order::where('admin_idempotency_key', $key)->firstOrFail();
        $item = Item::where('order_id', $order->id)->firstOrFail();
        $this->assertSame('700', (string) $item->unit_price);
        $this->assertSame('4200', (string) $order->subtotal_net);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'from_status' => null, 'to_status' => 'függőben']);

        $this->actingAs($partner)->get(route('account.orders'))->assertOk()->assertSee($order->displayNumber());
        $this->actingAs($admin)->post(route('adminpanel.orders.store'), [
            'partner_id' => $partner->id, 'shipping_address_id' => $shipping->id,
            'payment_method' => 0, 'initial_status' => 'függőben', 'idempotency_key' => $key,
            'confirm_order' => '1',
            'lines' => [['product_id' => $product->id, 'quantity' => 3]],
        ])->assertRedirect();
        $this->assertSame(1, Order::where('admin_idempotency_key', $key)->count());
    }

    public function test_order_delete_is_reversible_data_preserving_and_hidden_from_default_list(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::create(['user_id' => $admin->id, 'name' => 'Megőrzött', 'email' => $admin->email, 'address' => 'Cím', 'total' => 1, 'status' => 'függőben']);

        $this->actingAs($admin)->delete(route('adminpanel.orders.destroy', $order))->assertRedirect();
        $this->assertSoftDeleted('orders', ['id' => $order->id]);
        $this->actingAs($admin)->get(route('adminpanel.orders'))->assertOk()->assertDontSee($order->displayNumber());
        $this->actingAs($admin)->get(route('adminpanel.orders', ['include_deleted' => 1]))->assertOk()->assertSee($order->displayNumber());
    }
}
