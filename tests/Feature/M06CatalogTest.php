<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Products;
use App\Models\product_unit;
use App\Services\CategoryTreeService;
use App\Services\FilterCatalogService;
use App\Services\ProductCatalogService;
use App\Services\UnitCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class M06CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_tree_supports_unrestricted_depth_and_rejects_cycles(): void
    {
        $tree = app(CategoryTreeService::class);
        $parent = null;

        foreach (range(1, 5) as $level) {
            $parent = $tree->create([
                'name' => 'Szint '.$level,
                'parent_id' => $parent?->id,
            ]);
        }

        $this->assertCount(5, $tree->flatten($tree->roots()));

        $this->expectException(ValidationException::class);
        $tree->update($parent, [
            'name' => $parent->name,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_filter_definitions_are_inherited_from_all_ancestors_without_duplicates(): void
    {
        $tree = app(CategoryTreeService::class);
        $root = $tree->create(['name' => 'Villanyszerelés']);
        $child = $tree->create(['name' => 'Kábelek', 'parent_id' => $root->id]);
        $leaf = $tree->create(['name' => 'Adatkábelek', 'parent_id' => $child->id]);

        $definition = app(FilterCatalogService::class)->createDefinition([
            'name' => 'Gyártó',
            'category_ids' => [$root->id, $child->id],
            'values' => ['ACME'],
        ]);

        $definitions = app(FilterCatalogService::class)->definitionsForCategory($leaf);

        $this->assertCount(1, $definitions);
        $this->assertSame($definition->id, $definitions[0]->id);
        $this->assertSame('ACME', $definitions[0]->values->first()->label);
    }

    public function test_product_archive_preserves_the_product_record_and_hides_it_from_published_scope(): void
    {
        $category = Category::create(['name' => 'Kapcsolók']);
        $unit = product_unit::create(['unit' => 'Doboz', 'quantity' => 1, 'measure' => 'db']);
        $product = Products::create([
            'serial_number' => 'M06-ARCHIVE',
            'title' => 'Archiválható termék',
            'price' => 1000,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        app(ProductCatalogService::class)->archive($product);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_archived' => 1,
            'is_published' => 0,
        ]);
        $this->assertDatabaseCount('products', 1);
        $this->assertCount(0, Products::published()->get());
    }

    public function test_category_deletion_is_protected_when_it_has_children_or_products(): void
    {
        $tree = app(CategoryTreeService::class);
        $category = $tree->create(['name' => 'Védett kategória']);
        $child = $tree->create(['name' => 'Gyermek', 'parent_id' => $category->id]);

        $this->expectException(ValidationException::class);
        $tree->delete($category);
    }

    public function test_unit_deletion_is_protected_when_still_used(): void
    {
        $tree = app(CategoryTreeService::class);
        $category = $tree->create(['name' => 'Egység kategória']);

        $unit = product_unit::create(['unit' => 'Tekercs', 'quantity' => 1, 'measure' => 'm']);
        Products::create([
            'serial_number' => 'M06-UNIT',
            'title' => 'Egységhez kötött termék',
            'price' => 1000,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        $this->expectException(ValidationException::class);
        app(UnitCatalogService::class)->delete($unit);
    }

    public function test_catalog_query_includes_category_descendants_but_excludes_archived_products(): void
    {
        $tree = app(CategoryTreeService::class);
        $root = $tree->create(['name' => 'Kamerák']);
        $child = $tree->create(['name' => 'IP kamerák', 'parent_id' => $root->id]);
        $unit = product_unit::create(['unit' => 'Darab', 'quantity' => 1, 'measure' => 'db']);

        Products::create([
            'serial_number' => 'M06-LIVE',
            'title' => 'Publikus kamera',
            'price' => 1000,
            'category_id' => $child->id,
            'unit_id' => $unit->id,
        ]);
        Products::create([
            'serial_number' => 'M06-HIDDEN',
            'title' => 'Archivált kamera',
            'price' => 1000,
            'category_id' => $child->id,
            'unit_id' => $unit->id,
            'is_archived' => true,
            'is_published' => false,
        ]);

        $products = app(ProductCatalogService::class)->query(['category_id' => $root->id])->get();

        $this->assertCount(1, $products);
        $this->assertSame('M06-LIVE', $products->first()->serial_number);
    }
}
