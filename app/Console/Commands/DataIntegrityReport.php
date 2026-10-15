<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class DataIntegrityReport extends Command
{
    protected $signature = 'data:integrity-report {--json : JSON kimenet gépi feldolgozáshoz}';

    protected $description = 'Összesíti a kritikus üzleti táblák próbamigráció előtti egyeztetési mutatóit.';

    public function handle(): int
    {
        $tables = ['users', 'products', 'orders', 'items', 'special_prices', 'product_media', 'carts', 'tickets', 'offers'];
        $report = ['generated_at' => now()->toIso8601String(), 'environment' => app()->environment(), 'tables' => []];

        foreach ($tables as $table) {
            $report['tables'][$table] = Schema::hasTable($table) ? DB::table($table)->count() : null;
        }

        $report['orders'] = [
            'legacy_total_sum' => $this->sumIfPresent('orders', 'total'),
            'subtotal_net_sum' => $this->sumIfPresent('orders', 'subtotal_net'),
            'gross_total_sum' => $this->sumIfPresent('orders', 'gross_total'),
            'item_count' => Schema::hasTable('items') ? DB::table('items')->count() : null,
        ];
        $report['pricing'] = [
            'special_price_rows' => $report['tables']['special_prices'],
            'distinct_users' => Schema::hasTable('special_prices') ? DB::table('special_prices')->distinct('user_id')->count('user_id') : null,
            'distinct_products' => Schema::hasTable('special_prices') ? DB::table('special_prices')->distinct('product_id')->count('product_id') : null,
        ];
        $report['consistent'] = ! in_array(null, $report['tables'], true);

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(['Mutató', 'Érték'], $this->flatten($report));
        }

        return $report['consistent'] ? self::SUCCESS : self::FAILURE;
    }

    private function sumIfPresent(string $table, string $column): ?int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return null;
        }

        return (int) DB::table($table)->sum($column);
    }

    private function flatten(array $report): array
    {
        $rows = [['generated_at', $report['generated_at']], ['environment', $report['environment']], ['consistent', $report['consistent'] ? 'yes' : 'no']];
        foreach ($report['tables'] as $table => $count) {
            $rows[] = ['table.'.$table, $count ?? 'missing'];
        }
        foreach ($report['orders'] as $key => $value) {
            $rows[] = ['orders.'.$key, $value ?? 'missing'];
        }
        foreach ($report['pricing'] as $key => $value) {
            $rows[] = ['pricing.'.$key, $value ?? 'missing'];
        }

        return $rows;
    }
}
