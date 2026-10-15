<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Products;
use App\Models\Special_prices;
use App\Models\product_unit;
use App\Models\User;
use App\Services\AdminTransferQueryService;
use App\Services\OfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class M20OfferTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_publish_an_offer_without_changing_pricing(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
        $partner = User::factory()->create(['account_status' => 'active']);
        [$category, $product] = $this->catalogBase();
        Special_prices::create(['user_id' => $partner->id, 'product_id' => $product->id, 'price' => 777, 'extra_percent' => 0]);

        $this->actingAs($admin)->post(route('adminpanel.offers.store'), [
            'title' => 'M20 tavaszi ajánlat', 'slug' => 'm20-tavaszi', 'summary' => 'Kiemelt termékek.',
            'product_ids' => [$product->id], 'category_ids' => [$category->id], 'is_published' => 1,
            'starts_at' => Carbon::now()->subHour()->format('Y-m-d H:i'),
            'ends_at' => Carbon::now()->addDay()->format('Y-m-d H:i'),
        ])->assertRedirect();

        $offer = Offer::with(['products', 'categories'])->firstOrFail();
        $this->assertTrue($offer->is_published);
        $this->assertSame($product->id, $offer->products->first()->id);
        $this->assertSame($category->id, $offer->categories->first()->id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'offer.created', 'auditable_id' => $offer->id]);
        $this->actingAs($admin)->get(route('adminpanel.offers'))->assertOk()->assertSee('M20 tavaszi ajánlat');

        $this->get(route('info.offers'))->assertOk()->assertSee('M20 tavaszi ajánlat')->assertDontSee('777 Ft');
        $this->actingAs($partner)->get(route('info.offers'))->assertOk()->assertSee('M20 tavaszi ajánlat')->assertSee('777 Ft');
        $this->assertSame(777, $product->getPriceForUser($partner->id));
    }

    public function test_offer_schedule_and_cache_invalidation_control_public_visibility(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);
        [$category, $product] = $this->catalogBase('M20 időzítés');
        $offer = Offer::create([
            'title' => 'M20 későbbi ajánlat', 'slug' => 'm20-kesobbi', 'is_published' => true,
            'starts_at' => now()->addDay(), 'created_by' => $admin->id,
        ]);
        $offer->products()->attach($product->id);

        $this->get(route('info.offers'))->assertOk()->assertDontSee('M20 későbbi ajánlat');
        app(OfferService::class)->setPublished($offer, false, $admin);
        app(OfferService::class)->setPublished($offer, true, $admin);
        $offer->update(['starts_at' => now()->subMinute()]);
        app(OfferService::class)->invalidate();
        $this->get(route('info.offers'))->assertOk()->assertSee('M20 későbbi ajánlat');
    }

    public function test_offer_export_contains_content_metadata_without_prices(): void
    {
        [$category, $product] = $this->catalogBase('M20 export');
        $offer = Offer::create(['title' => 'M20 export ajánlat', 'slug' => 'm20-export', 'is_published' => false]);
        $offer->products()->attach($product->id);
        $offer->categories()->attach($category->id);

        $catalog = app(\App\Services\AdminExportCatalogService::class);
        $this->assertContains('offers', $catalog->all()->pluck('key')->all());
        $headers = app(AdminTransferQueryService::class)->headers('offers');
        $rows = app(AdminTransferQueryService::class)->rows('offers')->take(1)->all();
        $this->assertSame('Ajánlat', $headers['title']);
        $this->assertSame('M20 export ajánlat', $rows[0][1]);
        $this->assertSame(1, $rows[0][3]);
        $this->assertSame(1, $rows[0][4]);
        $this->assertNotContains('777', $rows[0]);
    }

    private function catalogBase(string $prefix = 'M20'): array
    {
        $category = Category::create(['name' => $prefix.' kategória', 'slug' => strtolower(str_replace(' ', '-', $prefix)).'-kategoria', 'is_active' => true]);
        $unit = product_unit::create(['unit' => 'db', 'quantity' => 1, 'measure' => 'db', 'is_active' => true]);
        $product = Products::create([
            'serial_number' => $prefix.'-001', 'title' => $prefix.' termék', 'slug' => strtolower(str_replace(' ', '-', $prefix)).'-termek',
            'price' => 1000, 'category_id' => $category->id, 'unit_id' => $unit->id,
            'is_published' => true, 'is_archived' => false, 'availability_status' => 'in_stock',
        ]);

        return [$category, $product];
    }
}
