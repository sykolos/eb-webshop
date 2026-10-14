<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('products', ['serial_number', 'is_published', 'is_archived'], 'products_catalog_serial_visibility_index');
        $this->addIndexIfMissing('products', ['title', 'is_published', 'is_archived'], 'products_catalog_title_visibility_index');
    }

    public function down(): void
    {
        $this->dropIndexIfPresent('products', 'products_catalog_serial_visibility_index');
        $this->dropIndexIfPresent('products', 'products_catalog_title_visibility_index');
    }

    private function addIndexIfMissing(string $table, array $columns, string $name): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $column = $columns[0];
            DB::statement(sprintf(
                'CREATE INDEX `%s` ON `%s` (`%s`(100), `is_published`, `is_archived`)',
                $name,
                $table,
                $column
            ));
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name): void {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndexIfPresent(string $table, string $name): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
    }

    private function indexExists(string $table, string $name): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => $index->name === $name);
        }

        return (int) (DB::selectOne(
            'select count(1) as count from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ?',
            [DB::getDatabaseName(), $table, $name]
        )->count ?? 0) > 0;
    }
};
