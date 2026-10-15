<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Products;
use App\Models\User;
use App\Models\Category;
use App\Models\product_unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class M15DashboardTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_historical_net_line_values_and_excludes_deleted_orders(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);
        $partner = User::factory()->create(['account_status' => 'active']);
        $category = Category::create(['name' => 'M15 kategória', 'slug' => 'm15-kategoria', 'is_active' => true]);
        $unit = product_unit::create(['unit' => 'db', 'quantity' => 1, 'measure' => 'darab', 'is_active' => true]);
        $product = Products::create(['serial_number' => 'M15-001', 'title' => 'M15 termék', 'price' => 999, 'category_id' => $category->id, 'unit_id' => $unit->id, 'is_published' => true, 'is_archived' => false, 'availability_status' => 'in_stock']);

        $order = Order::create([
            'user_id' => $partner->id, 'name' => $partner->name, 'email' => $partner->email,
            'address' => 'Teszt utca 1.', 'total' => 1250, 'subtotal_net' => 1250,
            'status' => 'függőben', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('items')->insert(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 625, 'line_net' => 1250, 'created_at' => now(), 'updated_at' => now()]);

        $deleted = $order->replicate();
        $deleted->forceFill(['status' => 'függőben', 'total' => 99000, 'subtotal_net' => 99000, 'created_at' => now()])->save();
        $deleted->delete();

        $response = $this->actingAs($admin)->get(route('adminpanel'));
        $response->assertOk()->assertSee('1 250 Ft')->assertSee('M15 termék')->assertSee('Top termékek');
    }

    public function test_tracking_requires_consent_deduplicates_and_masks_sensitive_payload(): void
    {
        $this->withoutVite();
        $payload = ['email' => 'secret@example.com', 'price' => '1250', 'query' => 'kábel'];
        $event = ['event_uuid' => '8b0f2d55-0d59-4e78-8d43-6f64d94be111', 'event_name' => 'search', 'page_path' => '/kereses', 'payload' => $payload];

        $this->postJson(route('tracking.events'), $event)->assertNoContent();
        $this->assertDatabaseCount('activity_events', 0);

        $this->withHeaders(['X-EB-Analytics-Consent' => 'accepted'])->postJson(route('tracking.events'), $event)->assertNoContent();
        $this->withHeaders(['X-EB-Analytics-Consent' => 'accepted'])->postJson(route('tracking.events'), $event)->assertNoContent();

        $this->assertDatabaseCount('activity_events', 1);
        $this->assertDatabaseHas('activity_events', ['event_name' => 'search']);
        $stored = DB::table('activity_events')->first();
        $this->assertStringNotContainsString('secret@example.com', (string) $stored->payload);
        $this->assertStringNotContainsString('1250', (string) $stored->payload);
        $this->assertSame('kábel', json_decode($stored->payload, true)['query']);
    }
}
