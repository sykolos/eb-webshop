<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Products;
use App\Models\Special_prices;
use App\Models\product_unit;
use App\Models\User;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class M07PricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_preserves_fixed_price_precedence_and_additive_discounts(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'M07 kategória']);
        $unit = product_unit::create(['unit' => 'db', 'quantity' => 1, 'measure' => 'db']);
        $product = Products::create([
            'serial_number' => 'M07-001',
            'title' => 'M07 teszttermék',
            'price' => 1000,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        DB::table('group_discounts')->insert([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'discount_percent' => 10,
            'extra_percent' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Special_prices::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'price' => 0,
            'extra_percent' => 5,
        ]);

        $pricing = app(PricingService::class);
        $this->assertSame(850, $pricing->priceForUser($product, $user->id));

        Special_prices::whereKey(Special_prices::first()->id)->update(['price' => 777]);
        $pricing->invalidateUser($user->id);

        $this->assertSame(777, $pricing->priceForUser($product, $user->id));
    }

    public function test_pricing_uses_fixed_point_rounding_and_pack_quote(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'M07 tizedes']);
        $unit = product_unit::create(['unit' => 'csomag', 'quantity' => 12, 'measure' => 'db']);
        $product = Products::create([
            'serial_number' => 'M07-002',
            'title' => 'M07 tizedes termék',
            'price' => 999.99,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        DB::table('group_discounts')->insert([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'discount_percent' => 10,
            'extra_percent' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Special_prices::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'price' => 0,
            'extra_percent' => 5,
        ]);

        $quote = app(PricingService::class)->quote($product, 2, $user->id);

        $this->assertSame('849.99', $quote['unit_net']);
        $this->assertSame('20399.76', $quote['line_net']);
        $this->assertSame('5507.94', $quote['vat']);
        $this->assertSame('25907.70', $quote['line_gross']);
    }

    public function test_pricing_context_is_cached_per_partner_and_invalidated_after_save(): void
    {
        Cache::flush();
        $user = User::factory()->create();
        $category = Category::create(['name' => 'M07 cache']);
        $unit = product_unit::create(['unit' => 'db', 'quantity' => 1, 'measure' => 'db']);
        $product = Products::create([
            'serial_number' => 'M07-003',
            'title' => 'M07 cache termék',
            'price' => 1000,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        DB::table('group_discounts')->insert([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'discount_percent' => 10,
            'extra_percent' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pricing = app(PricingService::class);
        $this->assertSame(900, $pricing->priceForUser($product, $user->id));
        DB::table('group_discounts')->where('user_id', $user->id)->update(['discount_percent' => 20]);
        $this->assertSame(900, $pricing->priceForUser($product, $user->id));
        $pricing->invalidateUser($user->id);
        $this->assertSame(800, $pricing->priceForUser($product, $user->id));
    }

    public function test_admin_matrix_returns_server_calculated_price(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'M07 admin']);
        $unit = product_unit::create(['unit' => 'db', 'quantity' => 1, 'measure' => 'db']);
        $product = Products::create([
            'serial_number' => 'M07-004',
            'title' => 'M07 admin termék',
            'price' => 1000,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        DB::table('group_discounts')->insert([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'discount_percent' => 10,
            'extra_percent' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson('/adminpanel/special-prices/matrix-ajax/'.$admin->id);

        $response->assertOk()->assertJsonPath('products.data.0.pricing.final_price', 900);
        $this->assertArrayNotHasKey('special_prices', $response->json('products.data.0'));
    }

    public function test_price_many_uses_one_context_for_the_product_collection(): void
    {
        Cache::flush();
        $user = User::factory()->create();
        $category = Category::create(['name' => 'M07 N+1']);
        $unit = product_unit::create(['unit' => 'db', 'quantity' => 1, 'measure' => 'db']);
        $products = collect(range(1, 3))->map(function (int $index) use ($category, $unit) {
            return Products::create([
                'serial_number' => 'M07-N1-'.$index,
                'title' => 'M07 N+1 '.$index,
                'price' => 1000 + $index,
                'category_id' => $category->id,
                'unit_id' => $unit->id,
            ]);
        });
        DB::table('group_discounts')->insert([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'discount_percent' => 10,
            'extra_percent' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $prices = app(PricingService::class)->priceMany($products, $user->id);

        $this->assertCount(3, $prices);
        $this->assertSame(2, count(DB::getQueryLog()));
    }
}
