<?php

namespace App\Http\Controllers;

use App\Models\AdminExport;
use App\Models\AdminImport;
use App\Services\AdminExportCatalogService;
use App\Services\AdminExportService;
use App\Services\AdminProductImportService;
use App\Services\CategoryTreeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminExportController extends Controller
{
    public function index(AdminExportCatalogService $catalog, AdminProductImportService $imports, CategoryTreeService $tree)
    {
        return view('admin.m13.exports.index', [
            'datasets' => $catalog->all(),
            'categories' => $tree->flatten($tree->roots()),
            'importFields' => $imports->fields(),
            'exports' => AdminExport::latest()->limit(12)->get(),
            'imports' => AdminImport::latest()->limit(8)->get(),
        ]);
    }

    public function store(Request $request, AdminExportService $exports)
    {
        $data = $request->validate([
            'dataset' => ['required', 'string', Rule::in(collect(config('admin_exports.datasets', []))->pluck('key')->all())],
            'format' => ['required', Rule::in(['csv', 'xlsx'])],
            'search' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'string', 'max:40'],
            'role' => ['nullable', 'in:partner,administrator'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'published' => ['nullable', 'boolean'],
            'include_archived' => ['nullable', 'boolean'],
            'include_deleted' => ['nullable', 'boolean'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
        $filters = collect($data)->except(['dataset', 'format'])->filter(fn ($value) => $value !== null && $value !== '')->all();
        $export = $exports->create($request->user(), $data['dataset'], $data['format'], $filters);

        return back()->with('success', 'Az export feldolgozása elindult #'.$export->id.'. A kész fájl lejáró, privát letöltésként jelenik meg.');
    }

    public function download(AdminExport $export, AdminExportService $exports)
    {
        return $exports->download($export);
    }

    public function importPreview(Request $request, AdminProductImportService $imports)
    {
        $request->validate(['file' => ['required', 'file', 'max:5120'], 'sheet' => ['nullable', 'integer', 'min:0'], 'mode' => ['nullable', Rule::in(['skip', 'update'])]]);
        try {
            $file = $request->file('file');
            $preview = $imports->createPreview($file, (int) $request->input('sheet', 0), null, $request->input('mode', 'skip'));
            $import = $imports->storePreview($request->user(), $file, $preview);
            return redirect()->route('adminpanel.exports.import.preview', $import);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            return back()->withErrors(['file' => $exception->getMessage() ?: 'Az import nem olvasható.']);
        }
    }

    public function importView(AdminImport $import, AdminProductImportService $imports)
    {
        return view('admin.m13.exports.import-preview', ['import' => $import, 'payload' => $imports->payload($import), 'fields' => $imports->fields()]);
    }

    public function importRemap(Request $request, AdminImport $import, AdminProductImportService $imports)
    {
        $data = $request->validate([
            'sheet' => ['required', 'integer', 'min:0'],
            'mode' => ['required', Rule::in(['skip', 'update'])],
            'mapping' => ['required', 'array'],
            'mapping.*' => ['nullable', 'integer', 'min:0', 'max:49'],
        ]);
        $imports->remapPreview($import, (int) $data['sheet'], $data['mapping'], $data['mode']);
        return back()->with('success', 'Az oszloppárosítás és a munkalap újraellenőrizve.');
    }

    public function importApply(Request $request, AdminImport $import, AdminProductImportService $imports)
    {
        try {
            $imports->apply($import, $request->user());
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('adminpanel.exports')->with('success', 'A termékimport alkalmazása sikeresen befejeződött.');
    }

    public function importErrors(AdminImport $import, AdminProductImportService $imports)
    {
        $payload = $imports->payload($import);
        return response()->streamDownload(function () use ($payload): void {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['Excel-sor', 'Cikkszám', 'Termék', 'Hibák'], ';', '"', '\\');
            foreach ($payload['rows'] as $row) if ($row['errors'] !== []) fputcsv($out, [$row['line'], $row['record']['serial_number'], $row['record']['title'], implode(' | ', $row['errors'])], ';', '"', '\\');
            fclose($out);
        }, 'eb-termekimport-hibak-'.$import->id.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function template(AdminProductImportService $imports)
    {
        abort_unless(class_exists(\ZipArchive::class), 503, 'Az XLSX sablonhoz a PHP zip kiterjesztése szükséges.');
        $path = tempnam(sys_get_temp_dir(), 'eb-template-');
        abort_if($path === false, 503, 'Ideiglenes XLSX fájl nem hozható létre.');
        $book = $imports->template();
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
        return response()->download($path, 'eb-termekimport-sablon.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
    }
}
