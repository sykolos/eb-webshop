<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class CapacityReport extends Command
{
    protected $signature = 'system:capacity-report {--json : JSON kimenet gépi feldolgozáshoz}';

    protected $description = 'Jelenti a média-, log- és privát fájltár aktuális kapacitási mutatóit.';

    public function handle(): int
    {
        $report = [
            'generated_at' => now()->toIso8601String(),
            'storage_path' => $this->pathReport(storage_path()),
            'public_disk' => $this->diskReport('public'),
            'private_disk' => $this->diskReport('private'),
            'log_bytes' => $this->directorySize(storage_path('logs')),
            'app_environment' => app()->environment(),
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(['Mutató', 'Érték'], [
                ['environment', $report['app_environment']],
                ['storage.free_bytes', $report['storage_path']['free_bytes']],
                ['storage.total_bytes', $report['storage_path']['total_bytes']],
                ['public.files', $report['public_disk']['files']],
                ['private.files', $report['private_disk']['files']],
                ['logs.bytes', $report['log_bytes']],
            ]);
        }

        return self::SUCCESS;
    }

    private function diskReport(string $disk): array
    {
        $files = Storage::disk($disk)->allFiles();
        return ['files' => count($files), 'bytes' => array_sum(array_map(fn (string $file): int => (int) (Storage::disk($disk)->size($file) ?: 0), $files))];
    }

    private function pathReport(string $path): array
    {
        return ['free_bytes' => (int) @disk_free_space($path), 'total_bytes' => (int) @disk_total_space($path)];
    }

    private function directorySize(string $directory): int
    {
        if (! is_dir($directory)) return 0;
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) $size += $file->getSize();
        }
        return $size;
    }
}
