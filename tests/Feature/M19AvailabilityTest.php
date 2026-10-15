<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Products;
use App\Models\product_unit;
use App\Services\AdminDashboardService;
use App\Services\AdminProductImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class M19AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_availability_and_history_is_audited(): void
    {
        $admin = $this->admin();
        [$category, $unit] = $this->catalogBase();
        $product = Products::create([
            'serial_number' => 'M19-001', 'title' => 'M19 státusz termék', 'slug' => 'm19-statusz-termek',
            'price' => 1000, 'category_id' => $category->id, 'unit_id' => $unit->id,
            'is_published' => true, 'is_archived' => false, 'availability_status' => 'in_stock',
        ]);

        $this->actingAs($admin)->put(route('adminpanel.products.update', $product->id), [
            'title' => $product->title, 'serialnum' => $product->serial_number, 'slug' => $product->slug,
            'manufacturer' => '', 'price' => 1000, 'category_id' => $category->id, 'unit_id' => $unit->id,
            'availability_status' => 'supplier_stock', 'is_published' => 1, 'description' => '',
            'specifications_text' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'availability_status' => 'supplier_stock']);
        $this->assertDatabaseHas('product_availability_histories', [
            'product_id' => $product->id, 'from_status' => 'in_stock', 'to_status' => 'supplier_stock', 'changed_by_user_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'product.availability_changed', 'auditable_id' => $product->id]);
        $this->actingAs($admin)->get(route('adminpanel.products.edit', $product->id))->assertOk()->assertSee('Beszállítói készleten');
    }

    public function test_status_is_visible_publicly_and_counted_on_dashboard_without_changing_publish_state(): void
    {
        $admin = $this->admin();
        [$category, $unit] = $this->catalogBase();
        $product = Products::create([
            'serial_number' => 'M19-002', 'title' => 'M19 publikus státusz', 'slug' => 'm19-publikus-statusz',
            'price' => 1000, 'category_id' => $category->id, 'unit_id' => $unit->id,
            'is_published' => true, 'is_archived' => false, 'availability_status' => 'procurement',
        ]);

        $this->get(route('product.slug', $product->slug))->assertOk()->assertSee('Beszerzés alatt');
        $summary = app(AdminDashboardService::class)->summary(now()->subDay(), now());
        $this->assertSame(1, $summary['availability']['procurement']);
        $this->assertTrue((bool) Products::findOrFail($product->id)->is_published);
        $this->assertTrue($admin->hasPermission('admin.access'));
    }

    public function test_status_change_invalidates_dashboard_availability_cache(): void
    {
        $admin = $this->admin();
        [$category, $unit] = $this->catalogBase();
        $product = Products::create([
            'serial_number' => 'M19-004', 'title' => 'M19 cache termék', 'slug' => 'm19-cache-termek',
            'price' => 1000, 'category_id' => $category->id, 'unit_id' => $unit->id,
            'is_published' => true, 'is_archived' => false, 'availability_status' => 'in_stock',
        ]);
        $from = now()->subDay();
        $to = now();
        $dashboard = app(AdminDashboardService::class);

        $before = $dashboard->summary($from, $to);
        $this->assertSame(1, $before['availability']['in_stock']);

        $this->actingAs($admin);
        app(\App\Services\ProductCatalogService::class)->setAvailabilityStatus($product, 'supplier_stock', $admin);

        $after = $dashboard->summary($from, $to);
        $this->assertSame(1, $after['availability']['supplier_stock']);
        $this->assertArrayNotHasKey('in_stock', $after['availability']);
    }

    public function test_product_import_accepts_human_status_label(): void
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('A helyi PHP-ben nincs zip kiterjesztés; az XLSX ág a CI/staging PHP-ban fut.');
        }

        [$category, $unit] = $this->catalogBase();
        $book = new Spreadsheet();
        $book->getActiveSheet()->fromArray([
            ['Cikkszám', 'Terméknév', 'Kategória', 'Nettó egységár', 'Alapegység', 'Elérhetőségi státusz'],
            ['M19-003', 'Import státusz', $category->name, '1250', $unit->unit, 'Beszerzés alatt'],
        ], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'm19-test-');
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();

        $preview = app(AdminProductImportService::class)->createPreview(
            UploadedFile::fake()->createWithContent('m19.xlsx', file_get_contents($path)),
        );

        $this->assertSame(0, $preview['summary']['errors']);
        $this->assertSame('procurement', $preview['rows'][0]['record']['availability_status']);
        @unlink($path);
    }

    private function admin()
    {
        return \App\Models\User::factory()->create(['is_admin' => true, 'role' => 'administrator', 'account_status' => 'active']);
    }

    private function catalogBase(): array
    {
        return [
            Category::create(['name' => 'M19 kategória', 'slug' => 'm19-kategoria', 'is_active' => true]),
            product_unit::create(['unit' => 'db', 'quantity' => 1, 'measure' => 'darab', 'is_active' => true]),
        ];
    }
}
