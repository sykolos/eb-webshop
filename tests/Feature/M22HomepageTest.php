<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Products;
use App\Models\RecommendedProduct;
use App\Models\User;
use App\Models\product_unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M22HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_v6_homepage_without_partner_price(): void
    {
        $this->withoutVite();
        [$category, $product] = $this->homepageProduct();
        RecommendedProduct::create(['product_id' => $product->id, 'sort_order' => 1]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-v6-body')
            ->assertSee('Kiemelt termékek')
            ->assertSee($category->name)
            ->assertSee('Partnerár belépés után')
            ->assertDontSee('1 234 Ft')
            ->assertDontSee('product-box')
            ->assertDontSee('category-box');
    }

    public function test_authenticated_partner_sees_own_homepage_price_and_cart_control(): void
    {
        $this->withoutVite();
        [$category, $product] = $this->homepageProduct('M22 partner');
        RecommendedProduct::create(['product_id' => $product->id, 'sort_order' => 1]);
        $partner = User::factory()->create(['account_status' => 'active']);

        $this->actingAs($partner)->get(route('home'))
            ->assertOk()
            ->assertSee('1 234 Ft')
            ->assertSee('data-home-cart-form')
            ->assertSee($category->name);
    }

    private function homepageProduct(string $prefix = 'M22'): array
    {
        $category = Category::create([
            'name' => $prefix.' kategória',
            'slug' => strtolower(str_replace(' ', '-', $prefix)).'-kategoria',
            'is_active' => true,
        ]);
        $unit = product_unit::create(['unit' => 'Darab', 'quantity' => 1, 'measure' => 'db', 'is_active' => true]);
        $product = Products::create([
            'serial_number' => $prefix.'-001',
            'title' => $prefix.' teszttermék',
            'slug' => strtolower(str_replace(' ', '-', $prefix)).'-termek',
            'price' => 1234,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'is_published' => true,
            'is_archived' => false,
            'availability_status' => 'in_stock',
        ]);

        return [$category, $product];
    }
}
