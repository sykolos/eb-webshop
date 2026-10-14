<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Products;
use App\Models\product_unit;
use App\Models\RecommendedProduct;
use App\Models\Special_prices;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M08CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_catalog_and_product_without_seeing_price(): void
    {
        $this->withoutVite();
        [$product, $partner] = $this->createProduct();
        Special_prices::create([
            'user_id' => $partner->id,
            'product_id' => $product->id,
            'price' => 777,
            'extra_percent' => 0,
        ]);

        $this->get(route('orderpage'))->assertOk()->assertDontSee('777 Ft');
        $this->get(route('product.slug', $product->slug))
            ->assertOk()
            ->assertSee('Partnerár és vásárlás belépés után')
            ->assertDontSee('777 Ft');

        $this->actingAs($partner)
            ->get(route('product.slug', $product->slug))
            ->assertOk()
            ->assertSee('777 Ft');
    }

    public function test_autocomplete_is_limited_and_price_free(): void
    {
        $this->withoutVite();
        [$product] = $this->createProduct();

        $response = $this->getJson(route('search.autocomplete', ['q' => $product->serial_number]));

        $response->assertOk()->assertJsonCount(1, 'results')->assertJsonPath('results.0.serial_number', $product->serial_number);
        $this->assertArrayNotHasKey('price', $response->json('results.0'));
    }

    public function test_search_redirects_to_url_backed_catalog_state_and_archived_product_is_hidden(): void
    {
        $this->withoutVite();
        [$product] = $this->createProduct();

        $this->get(route('search', ['query' => 'M08']))
            ->assertRedirect(route('orderpage', ['search' => 'M08']));

        $product->update(['is_published' => false]);
        $this->get(route('product.slug', $product->slug))->assertNotFound();
    }

    public function test_admin_can_persist_manual_featured_product_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        [$first] = $this->createProduct();
        [$second] = $this->createProduct();

        $this->actingAs($admin)->post(route('adminpanel.recommended.update'), [
            'products' => [$second->id, $first->id],
        ])->assertRedirect();

        $this->assertSame($second->id, RecommendedProduct::orderBy('sort_order')->first()->product_id);
        $this->assertSame($first->id, RecommendedProduct::orderBy('sort_order')->skip(1)->first()->product_id);
    }

    private function createProduct(): array
    {
        $category = Category::create([
            'name' => 'M08 tesztkategória',
            'slug' => 'm08-tesztkategoria',
            'is_active' => true,
        ]);
        $unit = product_unit::create(['unit' => 'Darab', 'quantity' => 1, 'measure' => 'db']);
        $product = Products::create([
            'serial_number' => 'M08-'.uniqid(),
            'title' => 'M08 katalógus teszttermék',
            'slug' => 'm08-katalogus-'.uniqid(),
            'description' => 'Teszt termék',
            'price' => 1200,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_published' => true,
            'is_archived' => false,
            'availability_status' => 'supplier_stock',
        ]);
        $partner = User::factory()->create();

        return [$product, $partner];
    }
}
