<?php

namespace App\Services;

use Illuminate\Support\Collection;
use InvalidArgumentException;

final class AdminExportCatalogService
{
    public function all(): Collection
    {
        return collect(config('admin_exports.datasets', []));
    }

    public function find(string $key): array
    {
        $dataset = $this->all()->firstWhere('key', $key);

        if (! is_array($dataset)) {
            throw new InvalidArgumentException('Ismeretlen admin export adatkör: '.$key);
        }

        return $dataset;
    }
}
