<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AdminExportCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M13ExportCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_complete_export_catalog_without_starting_a_download(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);

        $response = $this->actingAs($admin)->get(route('adminpanel.exports'));

        $response->assertOk()
            ->assertSee('Exportkatalógus')
            ->assertSee('Rendelések')
            ->assertSee('Rendeléssorok')
            ->assertSee('Termékek')
            ->assertSee('Kategóriák')
            ->assertSee('Szűrők')
            ->assertSee('Partnerek')
            ->assertSee('Mennyiségi egységek')
            ->assertSee('Külön árak')
            ->assertSee('Kiemeltek')
            ->assertDontSee('download');
    }

    public function test_export_catalog_is_admin_only_and_has_stable_dataset_keys(): void
    {
        $this->withoutVite();

        $this->get(route('adminpanel.exports'))->assertRedirect(route('login'));

        $catalog = app(AdminExportCatalogService::class);
        $this->assertSame(
            ['orders', 'order_items', 'products', 'categories', 'filters', 'partners', 'units', 'special_prices', 'featured_products'],
            $catalog->all()->pluck('key')->all(),
        );
        $this->assertSame('special_prices', $catalog->find('special_prices')['key']);
    }
}
