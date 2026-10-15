<?php

namespace App\Services;

use App\Models\AdminImport;
use App\Models\Category;
use App\Models\Products;
use App\Models\product_unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

final class AdminProductImportService
{
    private const MAX_FILE_BYTES = 5242880;
    private const MAX_ROWS = 5000;
    private const MAX_COLUMNS = 50;

    public function fields(): array
    {
        return [
            ['key' => 'serial_number', 'label' => 'Cikkszám', 'required' => true, 'aliases' => ['sku', 'id']],
            ['key' => 'title', 'label' => 'Terméknév', 'required' => true, 'aliases' => ['név', 'name']],
            ['key' => 'category', 'label' => 'Kategória', 'required' => true, 'aliases' => ['category', 'kategóriaútvonal']],
            ['key' => 'price', 'label' => 'Nettó egységár', 'required' => true, 'aliases' => ['ár', 'net price', 'value']],
            ['key' => 'unit', 'label' => 'Alapegység', 'required' => false, 'aliases' => ['mennyiségi egység']],
            ['key' => 'pack_size', 'label' => 'Csomagszorzó', 'required' => false, 'aliases' => ['packsize', 'szorzó']],
            ['key' => 'pack_name', 'label' => 'Csomagolás', 'required' => false, 'aliases' => ['packname', 'csomagnév']],
            ['key' => 'manufacturer', 'label' => 'Gyártó', 'required' => false, 'aliases' => ['brand', 'gyártó']],
            ['key' => 'description', 'label' => 'Leírás', 'required' => false, 'aliases' => ['description']],
            ['key' => 'is_published', 'label' => 'Publikálási állapot', 'required' => false, 'aliases' => ['published', 'publikus']],
            ['key' => 'availability_status', 'label' => 'Elérhetőségi státusz', 'required' => false, 'aliases' => ['status', 'állapot', 'elérhetőség']],
        ];
    }

    public function createPreview(UploadedFile $file, int $sheetIndex = 0, ?array $mapping = null, string $mode = 'skip'): array
    {
        $sheets = $this->readWorkbook($file);
        $sheet = $sheets[$sheetIndex] ?? throw ValidationException::withMessages(['file' => 'A kiválasztott munkalap nem létezik.']);
        $headers = $sheet['rows'][0] ?? [];
        $mapping ??= $this->autoMapping($headers);
        $rows = $this->validateRows(array_slice($sheet['rows'], 1), $mapping, $mode);

        return [
            'sheet_index' => $sheetIndex,
            'sheet_name' => $sheet['name'],
            'headers' => $headers,
            'mapping' => $mapping,
            'mode' => $mode,
            'source_sheets' => $sheets,
            'rows' => $rows,
            'summary' => $this->summary($rows),
        ];
    }

    public function remapPreview(AdminImport $import, int $sheetIndex, array $mapping, string $mode): AdminImport
    {
        $payload = $this->payload($import);
        $sheet = $payload['source_sheets'][$sheetIndex] ?? throw ValidationException::withMessages(['mapping' => 'A kiválasztott munkalap nem létezik.']);
        $normalized = [];
        foreach ($this->fields() as $field) {
            $value = $mapping[$field['key']] ?? null;
            $normalized[$field['key']] = $value === '' || $value === null ? null : (int) $value;
        }
        $rows = $this->validateRows(array_slice($sheet['rows'], 1), $normalized, $mode);
        $payload['sheet_index'] = $sheetIndex;
        $payload['sheet_name'] = $sheet['name'];
        $payload['headers'] = $sheet['rows'][0] ?? [];
        $payload['mapping'] = $normalized;
        $payload['mode'] = $mode;
        $payload['rows'] = $rows;
        $payload['summary'] = $this->summary($rows);
        $import->update(['preview_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'summary' => $payload['summary'], 'status' => 'preview', 'error' => null]);
        return $import->refresh();
    }

    public function storePreview(\App\Models\User $user, UploadedFile $file, array $preview): AdminImport
    {
        return AdminImport::create([
            'user_id' => $user->id,
            'dataset' => 'products',
            'original_name' => $file->getClientOriginalName(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'status' => 'preview',
            'preview_payload' => json_encode($preview, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'summary' => $preview['summary'],
        ]);
    }

    public function payload(AdminImport $import): array
    {
        return json_decode($import->preview_payload, true, 512, JSON_THROW_ON_ERROR);
    }

    public function apply(AdminImport $import, \App\Models\User $actor): AdminImport
    {
        return DB::transaction(function () use ($import, $actor): AdminImport {
            $locked = AdminImport::query()->lockForUpdate()->findOrFail($import->id);
            if ($locked->status === 'applied') {
                return $locked;
            }

            $payload = $this->payload($locked);
            if (($payload['summary']['errors'] ?? 0) > 0) {
                throw ValidationException::withMessages(['import' => 'Hibás sorokat tartalmazó import nem alkalmazható. Előbb javítsd a fájlt.']);
            }

            $changed = 0;
            foreach ($payload['rows'] as $row) {
                if ($row['action'] === 'skip') {
                    continue;
                }
                $record = $row['record'];
                $product = ! empty($record['existing_id']) ? Products::lockForUpdate()->find($record['existing_id']) : null;
                if ($product && $record['existing_updated_at'] && optional($product->updated_at)->toISOString() !== $record['existing_updated_at']) {
                    throw ValidationException::withMessages(['import' => 'Konkurens változás történt a(z) '.$record['serial_number'].' terméknél; az importot nem alkalmaztam.']);
                }

                $duplicate = Products::whereRaw('LOWER(serial_number) = ?', [mb_strtolower($record['serial_number'])])
                    ->when($product, fn ($q) => $q->where('id', '!=', $product->id))->exists();
                if ($duplicate) {
                    throw ValidationException::withMessages(['import' => 'A cikkszám időközben már használatba került: '.$record['serial_number']]);
                }

                $attributes = [
                    'serial_number' => $record['serial_number'],
                    'title' => $record['title'],
                    'category_id' => $record['category_id'],
                    'price' => $record['price'],
                    'unit_id' => $this->unitId($record['unit'] ?: null, $product?->unit_id),
                ];
                if ($product === null || array_key_exists('manufacturer', $record['provided'])) {
                    if (array_key_exists('manufacturer', $record['provided'])) $attributes['manufacturer'] = $record['manufacturer'];
                }
                if ($product === null || array_key_exists('description', $record['provided'])) {
                    if (array_key_exists('description', $record['provided'])) $attributes['description'] = $record['description'];
                }
                if ($product === null || array_key_exists('is_published', $record['provided'])) {
                    if (array_key_exists('is_published', $record['provided'])) $attributes['is_published'] = $record['is_published'];
                }
                if ($product === null || array_key_exists('availability_status', $record['provided'])) {
                    if (array_key_exists('availability_status', $record['provided'])) $attributes['availability_status'] = $record['availability_status'];
                }
                $specifications = is_array($product?->specifications) ? $product->specifications : [];
                foreach (['pack_size', 'pack_name'] as $key) {
                    if (array_key_exists($key, $record['provided'])) $specifications[$key] = $record[$key];
                }
                if ($specifications !== []) $attributes['specifications'] = $specifications;

                if ($product) {
                    $product->update($attributes);
                } else {
                    $attributes['slug'] = $this->uniqueSlug($record['title']);
                    $attributes['is_published'] ??= true;
                    $attributes['availability_status'] ??= 'in_stock';
                    Products::create($attributes);
                }
                $changed++;
            }

            $summary = $locked->summary ?? [];
            $summary['changed'] = $changed;
            $locked->update(['status' => 'applied', 'applied_at' => now(), 'summary' => $summary]);
            app(AuditLogger::class)->record('products.import.applied', $locked, ['import_id' => $locked->id, 'changed' => $changed], request());
            return $locked->refresh();
        });
    }

    public function template(): Spreadsheet
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Termékek');
        $sheet->fromArray(array_map(fn (array $field) => $field['label'], $this->fields()), null, 'A1');
        $sheet->fromArray(['MINTA-001', 'Minta termék', 'Kategóriaútvonal', 1250, 'db', 1, 'doboz', 'Minta gyártó', 'Minta leírás', 'Igen', 'Készleten'], null, 'A2');
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        return $book;
    }

    private function readWorkbook(UploadedFile $file): array
    {
        if ($file->getSize() > self::MAX_FILE_BYTES) throw ValidationException::withMessages(['file' => 'A fájl legfeljebb 5 MB lehet.']);
        if (strtolower($file->getClientOriginalExtension()) === 'xls') throw ValidationException::withMessages(['file' => 'A régi .xls formátumot előbb mentsd .xlsx formátumba.']);
        if (strtolower($file->getClientOriginalExtension()) !== 'xlsx') throw ValidationException::withMessages(['file' => 'Csak .xlsx termékimport fogadható el.']);
        if (! class_exists(\ZipArchive::class)) throw ValidationException::withMessages(['file' => 'Az XLSX feldolgozásához a PHP zip kiterjesztése szükséges.']);

        try {
            $workbook = IOFactory::load($file->getRealPath());
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages(['file' => 'A fájl nem olvasható. Jelszó nélküli, érvényes .xlsx munkafüzetet válassz.']);
        }
        $sheets = [];
        foreach ($workbook->getWorksheetIterator() as $sheet) {
            if ($sheet->getHighestRow() > self::MAX_ROWS + 1 || Coordinate::columnIndexFromString($sheet->getHighestColumn()) > self::MAX_COLUMNS) throw ValidationException::withMessages(['file' => 'Egy munkalap legfeljebb 5000 adatsort és 50 oszlopot tartalmazhat.']);
            $rows = [];
            for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
                $cells = [];
                for ($column = 1; $column <= Coordinate::columnIndexFromString($sheet->getHighestColumn()); $column++) {
                    $cell = $sheet->getCellByColumnAndRow($column, $row);
                    $cells[] = $cell->getDataType() === 'f' ? ['formula' => true] : (string) ($cell->getValue() ?? '');
                }
                $rows[] = $cells;
            }
            $sheets[] = ['name' => $sheet->getTitle(), 'rows' => $rows];
        }
        if ($sheets === []) throw ValidationException::withMessages(['file' => 'A munkafüzet üres.']);
        return $sheets;
    }

    private function autoMapping(array $headers): array
    {
        $mapping = [];
        foreach ($this->fields() as $field) {
            $names = array_map([$this, 'normalize'], array_merge([$field['key'], $field['label']], $field['aliases']));
            $mapping[$field['key']] = null;
            foreach ($headers as $index => $header) if (in_array($this->normalize((string) $header), $names, true)) { $mapping[$field['key']] = $index; break; }
        }
        return $mapping;
    }

    private function validateRows(array $rawRows, array $mapping, string $mode): array
    {
        $mappedColumns = array_values(array_filter($mapping, fn ($column) => $column !== null));
        $mappingError = count($mappedColumns) !== count(array_unique($mappedColumns));
        $duplicateCounts = [];
        foreach ($rawRows as $row) {
            $serial = mb_strtolower(trim($this->text($row, $mapping, 'serial_number')));
            if ($serial !== '') $duplicateCounts[$serial] = ($duplicateCounts[$serial] ?? 0) + 1;
        }
        $existing = Products::query()->get()->keyBy(fn (Products $product) => mb_strtolower($product->serial_number));
        $categories = $this->categoryMap();
        $rows = [];
        foreach ($rawRows as $index => $raw) {
            if (! collect($raw)->contains(fn ($cell) => is_array($cell) || trim((string) $cell) !== '')) continue;
            $line = $index + 2;
            $errors = [];
            if ($mappingError) $errors[] = 'Egy oszlop több mezőhöz van rendelve.';
            $provided = [];
            $serial = trim($this->text($raw, $mapping, 'serial_number'));
            $key = mb_strtolower($serial);
            $old = $existing->get($key);
            if (($duplicateCounts[$key] ?? 0) > 1) $errors[] = 'Ismétlődő cikkszám a fájlban.';
            foreach ($this->fields() as $field) {
                $value = $this->text($raw, $mapping, $field['key'], $isProvided);
                if ($isProvided) $provided[$field['key']] = true;
                if ($field['required'] && $value === '') $errors[] = $field['label'].': kötelező.';
                if ($this->isFormula($raw, $mapping[$field['key']] ?? null)) $errors[] = $field['label'].': képlet nem fogadható el.';
                $values[$field['key']] = $value;
            }
            $categoryKey = mb_strtolower(trim($values['category']));
            $category = $this->resolveCategory($values['category'], $categories);
            if (! $category) {
                $errors[] = array_key_exists($categoryKey, $categories) && $categories[$categoryKey] === false
                    ? 'A kategória neve nem egyértelmű; használj teljes útvonalat vagy azonosítót: '.$values['category']
                    : 'Ismeretlen kategória: '.$values['category'];
            }
            $price = $this->decimal($values['price']);
            if ($price === null || $price < 0 || $price > 1000000000) $errors[] = 'Az ár 0 és 1 milliárd közötti szám lehet.';
            $status = $this->resolveStatus($values['availability_status']);
            if ($values['availability_status'] !== '' && $status === null) $errors[] = 'Ismeretlen elérhetőségi státusz.';
            $unit = trim($values['unit']);
            if ($unit !== '' && ! product_unit::whereRaw('LOWER(unit) = ?', [mb_strtolower($unit)])->exists()) $errors[] = 'Ismeretlen mennyiségi egység: '.$unit;
            foreach (['pack_size' => 0.01] as $field => $minimum) if ($values[$field] !== '' && (($number = $this->decimal($values[$field])) === null || $number < $minimum)) $errors[] = $field.': érvénytelen szám.';
            if ($values['is_published'] !== '' && $this->booleanValue($values['is_published']) === null) $errors[] = 'Ismeretlen publikálási állapot.';
            $record = ['existing_id' => $old?->id, 'existing_updated_at' => $old?->updated_at?->toISOString(), 'serial_number' => $serial, 'title' => trim($values['title']), 'category_id' => $category?->id, 'price' => $price === null ? null : (int) round($price), 'unit' => $unit, 'manufacturer' => trim($values['manufacturer']), 'description' => trim($values['description']), 'is_published' => $this->booleanValue($values['is_published']), 'availability_status' => $status, 'pack_size' => $values['pack_size'] === '' ? null : $this->decimal($values['pack_size']), 'pack_name' => trim($values['pack_name']), 'provided' => $provided];
            $rows[] = ['line' => $line, 'record' => $record, 'errors' => array_values(array_unique($errors)), 'action' => $old ? ($mode === 'update' ? 'update' : 'skip') : 'create'];
        }
        return $rows;
    }

    private function summary(array $rows): array
    {
        return [
            'total' => count($rows),
            'create' => count(array_filter($rows, fn (array $row) => $row['action'] === 'create' && $row['errors'] === [])),
            'update' => count(array_filter($rows, fn (array $row) => $row['action'] === 'update' && $row['errors'] === [])),
            'skip' => count(array_filter($rows, fn (array $row) => $row['action'] === 'skip' && $row['errors'] === [])),
            'errors' => count(array_filter($rows, fn (array $row) => $row['errors'] !== [])),
        ];
    }

    private function text(array $row, array $mapping, string $key, ?bool &$provided = null): string
    {
        $index = $mapping[$key] ?? null;
        $cell = $index === null ? null : ($row[$index] ?? null);
        $provided = $cell !== null && ! is_array($cell) && trim((string) $cell) !== '';
        return is_array($cell) ? '' : trim((string) ($cell ?? ''));
    }

    private function isFormula(array $row, ?int $index): bool
    {
        return $index !== null && is_array($row[$index] ?? null) && ! empty($row[$index]['formula']);
    }

    private function decimal(string $value): ?float
    {
        if ($value === '') return null;
        $clean = str_replace(["\xc2\xa0", ' ', ','], ['', '', '.'], $value);
        return preg_match('/^\d+(\.\d+)?$/', $clean) ? (float) $clean : null;
    }

    private function booleanValue(string $value): ?bool
    {
        if ($value === '') return null;
        return match (mb_strtolower($value)) { '1', 'igen', 'yes', 'true', 'publikus' => true, '0', 'nem', 'no', 'false', 'rejtett' => false, default => null };
    }

    private function resolveStatus(string $value): ?string
    {
        if ($value === '') return null;
        $normalized = mb_strtolower($value);
        foreach (Products::AVAILABILITY_STATUSES as $key => $label) if ($normalized === mb_strtolower($key) || $normalized === mb_strtolower($label)) return $key;
        return null;
    }

    private function categoryMap(): array
    {
        $categories = Category::all()->keyBy('id');
        $map = [];
        foreach ($categories as $category) {
            $path = [$category->name]; $parent = $categories->get($category->parent_id); $guard = 0;
            while ($parent && $guard++ < 100) { array_unshift($path, $parent->name); $parent = $categories->get($parent->parent_id); }
            $map[mb_strtolower(implode(' > ', $path))] = $category;
            $nameKey = mb_strtolower($category->name);
            if (! array_key_exists($nameKey, $map)) {
                $map[$nameKey] = $category;
            } elseif ($map[$nameKey] instanceof Category && $map[$nameKey]->id !== $category->id) {
                $map[$nameKey] = false;
            }
            $map[(string) $category->id] = $category;
        }
        return $map;
    }

    private function resolveCategory(string $value, array $map): ?Category
    {
        return $map[mb_strtolower(trim($value))] ?? $map[trim($value)] ?? null;
    }

    private function unitId(?string $value, ?int $current): int
    {
        $unit = $value ? product_unit::whereRaw('LOWER(unit) = ?', [mb_strtolower($value)])->first() : ($current ? product_unit::find($current) : product_unit::query()->where('is_active', true)->first());
        if (! $unit) throw ValidationException::withMessages(['import' => 'A termékhez nem található mennyiségi egység.']);
        return $unit->id;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'termek'; $slug = $base; $number = 2;
        while (Products::where('slug', $slug)->exists()) $slug = $base.'-'.($number++);
        return $slug;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(preg_replace('/[^\pL\pN]+/u', '', trim($value)) ?: '');
    }
}
