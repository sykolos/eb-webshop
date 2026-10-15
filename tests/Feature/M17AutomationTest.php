<?php

namespace Tests\Feature;

use App\Jobs\SendAutomationNotification;
use App\Jobs\SendCartReminder;
use App\Mail\AutomationNotificationMail;
use App\Mail\CartReminderMail;
use App\Models\AutomationRule;
use App\Models\AutomationRun;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Products;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\product_unit;
use App\Services\AutomationRuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class M17AutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_automation_center_and_rules_start_disabled(): void
    {
        $this->withoutVite();
        $this->get(route('adminpanel.automations'))->assertRedirect(route('login'));
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $this->actingAs($admin)->get(route('adminpanel.automations'))
            ->assertOk()
            ->assertSee('Automatizmusok')
            ->assertSee('Elhagyott kosár emlékeztető')
            ->assertSee('Szünetel');
        $this->assertDatabaseCount('automation_rules', 4);
        $this->assertDatabaseMissing('automation_rules', ['enabled' => true]);
    }

    public function test_stale_cart_is_queued_once_and_job_rechecks_activity_before_sending(): void
    {
        Queue::fake();
        Mail::fake();
        $user = User::factory()->create(['account_status' => 'active']);
        UserPreference::create(['user_id' => $user->id, 'marketing_notifications' => true, 'newsletter_subscribed' => false, 'order_updates' => true]);
        $product = $this->product('M17-001');
        $cart = Cart::create(['user_id' => $user->id, 'last_activity_at' => now()->subHours(3)]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 2]);
        $rule = AutomationRule::where('key', 'abandoned_cart')->firstOrFail();
        $rule->update(['enabled' => true, 'delay_minutes' => 60]);

        $service = app(AutomationRuleService::class);
        $this->assertSame(1, $service->dispatchDueCarts());
        $this->assertSame(0, $service->dispatchDueCarts());
        Queue::assertPushed(SendCartReminder::class, 1);
        $run = AutomationRun::firstOrFail();

        $cart->update(['last_activity_at' => now()]);
        (new SendCartReminder($run->id))->handle();
        $this->assertDatabaseHas('automation_runs', ['id' => $run->id, 'status' => 'skipped']);
        Mail::assertNothingSent();
    }

    public function test_valid_stale_cart_sends_and_suppression_skips_delivery(): void
    {
        Mail::fake();
        $user = User::factory()->create(['account_status' => 'active']);
        UserPreference::create(['user_id' => $user->id, 'marketing_notifications' => true, 'newsletter_subscribed' => false, 'order_updates' => true]);
        $product = $this->product('M17-002');
        $cart = Cart::create(['user_id' => $user->id, 'last_activity_at' => now()->subHours(3)]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1]);
        AutomationRule::where('key', 'abandoned_cart')->update(['enabled' => true, 'delay_minutes' => 60]);
        app(AutomationRuleService::class)->dispatchDueCarts();
        $run = AutomationRun::firstOrFail();
        (new SendCartReminder($run->id))->handle();
        Mail::assertSent(CartReminderMail::class, 1);
        $this->assertDatabaseHas('automation_runs', ['id' => $run->id, 'status' => 'sent']);
    }

    public function test_event_automation_is_idempotent_and_respects_order_preference(): void
    {
        Queue::fake();
        $user = User::factory()->create(['account_status' => 'active']);
        UserPreference::create(['user_id' => $user->id, 'marketing_notifications' => false, 'newsletter_subscribed' => false, 'order_updates' => false]);
        $rule = AutomationRule::where('key', 'partner_approved')->firstOrFail();
        $rule->update(['enabled' => true]);
        $service = app(AutomationRuleService::class);
        $first = $service->dispatchEvent('partner_approved', $user, ['registration_request_id' => 17]);
        $second = $service->dispatchEvent('partner_approved', $user, ['registration_request_id' => 17]);
        $this->assertNotNull($first);
        $this->assertTrue($first->is($second));
        Queue::assertPushed(SendAutomationNotification::class, 1);

        $statusRule = AutomationRule::where('key', 'order_status_changed')->firstOrFail();
        $statusRule->update(['enabled' => true]);
        $this->assertNull($service->dispatchEvent('order_status_changed', $user, ['order_id' => 9, 'history_id' => 10, 'status' => 'feldolgozás']));
        $this->assertDatabaseMissing('automation_runs', ['event' => 'order_status_changed']);
    }

    public function test_admin_can_toggle_rule_without_running_a_side_effect(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $rule = AutomationRule::where('key', 'abandoned_cart')->firstOrFail();
        $this->actingAs($admin)->post(route('adminpanel.automations.update', $rule), ['enabled' => 1, 'delay_minutes' => 90, 'max_per_user' => 2])->assertRedirect();
        $this->assertDatabaseHas('automation_rules', ['id' => $rule->id, 'enabled' => 1, 'delay_minutes' => 90, 'max_per_user' => 2]);
        $this->assertDatabaseCount('automation_runs', 0);
    }

    private function product(string $serial): Products
    {
        $category = Category::firstOrCreate(['slug' => 'm17-category'], ['name' => 'M17 kategória', 'is_active' => true]);
        $unit = product_unit::create(['unit' => 'Doboz', 'quantity' => 1, 'measure' => 'db', 'is_active' => true]);
        return Products::create(['serial_number' => $serial, 'title' => $serial.' termék', 'description' => 'M17 teszttermék', 'price' => 1000, 'category_id' => $category->id, 'unit_id' => $unit->id, 'is_published' => true, 'availability_status' => 'in_stock']);
    }
}
