<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AdminExport;
use App\Models\Category;
use App\Models\Products;
use App\Models\product_unit;
use App\Services\AdminExportCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
            ['orders', 'order_items', 'products', 'categories', 'filters', 'partners', 'units', 'special_prices', 'featured_products', 'banners', 'offers', 'carts', 'activity_events', 'email_campaigns', 'email_deliveries', 'automation_rules', 'automation_runs', 'tickets', 'ticket_messages'],
            $catalog->all()->pluck('key')->all(),
        );
        $this->assertSame('special_prices', $catalog->find('special_prices')['key']);
    }

    public function test_csv_export_uses_the_full_filtered_result_and_formula_safe_cells(): void
    {
        $this->withoutVite();
        config(['queue.default' => 'sync']);
        Storage::fake('private');
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);
        $category = Category::create(['name' => 'M13 kategória', 'slug' => 'm13-kategoria', 'is_active' => true]);
        $unit = product_unit::create(['unit' => 'db', 'quantity' => 1, 'measure' => 'darab', 'is_active' => true]);
        Products::create(['serial_number' => '=M13-001', 'title' => 'M13 export termék', 'slug' => 'm13-export-termek', 'category_id' => $category->id, 'unit_id' => $unit->id, 'price' => 1250, 'is_published' => true, 'availability_status' => 'in_stock']);
        Products::create(['serial_number' => 'OTHER-001', 'title' => 'Másik termék', 'slug' => 'masik-termek', 'category_id' => $category->id, 'unit_id' => $unit->id, 'price' => 2000, 'is_published' => true, 'availability_status' => 'in_stock']);

        $this->actingAs($admin)->post(route('adminpanel.exports.store'), [
            'dataset' => 'products', 'format' => 'csv', 'search' => 'M13 export',
        ])->assertRedirect();

        $export = AdminExport::latest()->firstOrFail();
        $this->assertSame('completed', $export->status);
        $this->assertSame(1, $export->rows_count);
        $csv = Storage::disk('private')->get($export->file_path);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString("'=M13-001", $csv);
        $this->assertStringNotContainsString('Művelet', $csv);
    }

    public function test_product_import_preview_rejects_formula_cells_and_requires_an_explicit_apply(): void
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('A helyi PHP-ben nincs zip kiterjesztés; az XLSX ág a CI/staging PHP-ban fut.');
        }

        $this->withoutVite();
        config(['queue.default' => 'sync']);
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'administrator']);
        $category = Category::create(['name' => 'Import kategória', 'slug' => 'import-kategoria', 'is_active' => true]);
        product_unit::create(['unit' => 'db', 'quantity' => 1, 'measure' => 'darab', 'is_active' => true]);
        $book = new Spreadsheet();
        $book->getActiveSheet()->fromArray([['Cikkszám', 'Terméknév', 'Kategória', 'Nettó egységár'], ['IMP-001', 'Importált termék', $category->name, '1 250'], ['=IMP-002', 'Képletes termék', $category->name, '2000']], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'm13-test-');
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();

        $this->actingAs($admin)->post(route('adminpanel.exports.import.preview.store'), [
            'file' => UploadedFile::fake()->createWithContent('products.xlsx', file_get_contents($path)), 'mode' => 'skip',
        ])->assertRedirect();

        $import = \App\Models\AdminImport::latest()->firstOrFail();
        $this->assertSame(1, $import->summary['errors']);
        $this->actingAs($admin)->post(route('adminpanel.exports.import.apply', $import))->assertSessionHasErrors('import');
        $this->assertDatabaseMissing('products', ['serial_number' => 'IMP-001']);
        @unlink($path);
    }
}
