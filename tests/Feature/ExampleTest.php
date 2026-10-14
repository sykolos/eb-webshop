<?php

namespace Tests\Feature;

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
}
