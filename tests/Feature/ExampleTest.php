<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
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
}
