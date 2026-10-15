<?php

namespace App\Services;

use App\Jobs\BuildAdminExport;
use App\Models\AdminExport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

final class AdminExportService
{
    public function __construct(private readonly AdminExportCatalogService $catalog, private readonly AdminTransferQueryService $queries)
    {
    }

    public function create(User $user, string $dataset, string $format, array $filters = []): AdminExport
    {
        $this->catalog->find($dataset);
        if (! in_array($format, ['csv', 'xlsx'], true)) {
            throw new RuntimeException('Az export formátuma csak CSV vagy XLSX lehet.');
        }

        $export = AdminExport::create([
            'user_id' => $user->id,
            'dataset' => $dataset,
            'format' => $format,
            'download_token' => (string) Str::uuid(),
            'filters' => $filters,
            'status' => 'queued',
            'expires_at' => now()->addHours(2),
        ]);

        BuildAdminExport::dispatch($export->id)->afterCommit();

        return $export->refresh();
    }

    public function build(AdminExport $export): AdminExport
    {
        $export->update(['status' => 'processing', 'started_at' => now(), 'error' => null]);
        $temp = tempnam(sys_get_temp_dir(), 'eb-export-');

        if ($temp === false) {
            throw new RuntimeException('Ideiglenes exportfájl nem hozható létre.');
        }

        try {
            $rows = 0;
            $headers = array_values($this->queries->headers($export->dataset));
            if ($export->format === 'csv') {
                $handle = fopen($temp, 'wb');
                if ($handle === false) {
                    throw new RuntimeException('CSV exportfájl nem nyitható meg.');
                }
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, $this->safeRow($headers), ';', '"', '\\');
                foreach ($this->queries->rows($export->dataset, $export->filters ?? []) as $row) {
                    fputcsv($handle, $this->safeRow($row), ';', '"', '\\');
                    $rows++;
                }
                fclose($handle);
            } else {
                if (! class_exists(\ZipArchive::class)) {
                    throw new RuntimeException('XLSX exporthez a PHP zip kiterjesztése szükséges.');
                }
                $book = new Spreadsheet();
                $book->getProperties()->setCreator('Electro Business')->setTitle($export->dataset);
                $sheet = $book->getActiveSheet();
                $sheet->setTitle('Adatok');
                $sheet->fromArray($this->safeRow($headers), null, 'A1');
                $sheet->getStyle('A1:'.$this->columnName(count($headers)).'1')->getFont()->setBold(true);
                foreach ($this->queries->rows($export->dataset, $export->filters ?? []) as $row) {
                    $sheet->fromArray($this->safeRow($row), null, 'A'.($rows + 2));
                    $rows++;
                }
                $sheet->freezePane('A2');
                $writer = new Xlsx($book);
                $writer->setPreCalculateFormulas(false);
                $writer->save($temp);
                $book->disconnectWorksheets();
                unset($book);
            }

            $path = 'exports/'.$export->download_token.'.'.$export->format;
            $stream = fopen($temp, 'rb');
            Storage::disk('private')->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            $export->update([
                'status' => 'completed',
                'file_path' => $path,
                'file_name' => 'eb-'.$export->dataset.'-'.now()->format('Ymd-His').'.'.$export->format,
                'rows_count' => $rows,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $export->update(['status' => 'failed', 'error' => $exception->getMessage(), 'completed_at' => now()]);
            throw $exception;
        } finally {
            @unlink($temp);
        }

        return $export->refresh();
    }

    public function download(AdminExport $export)
    {
        abort_unless($export->isDownloadable(), 410, 'Az export lejárt vagy még nem készült el.');
        abort_unless(Storage::disk('private')->exists($export->file_path), 404);

        return Storage::disk('private')->download($export->file_path, $export->file_name);
    }

    public function cleanupExpired(): int
    {
        $count = 0;
        AdminExport::where('expires_at', '<', now())->whereNotNull('file_path')->each(function (AdminExport $export) use (&$count): void {
            Storage::disk('private')->delete($export->file_path);
            $export->update(['status' => 'expired', 'file_path' => null]);
            $count++;
        });

        return $count;
    }

    private function safeRow(array $row): array
    {
        return array_map(function ($value): string {
            if ($value === null) {
                return '';
            }
            $value = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return preg_match('/^[\s]*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
        }, array_values($row));
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }
        return $name ?: 'A';
    }
}
