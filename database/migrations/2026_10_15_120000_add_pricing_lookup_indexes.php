<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('special_prices', ['user_id', 'product_id'], 'special_prices_user_product_index');
        $this->addIndexIfMissing('group_discounts', ['user_id', 'category_id'], 'group_discounts_user_category_lookup');
        $this->addIndexIfMissing('products', ['category_id', 'is_published', 'is_archived'], 'products_catalog_visibility_index');
    }

    public function down(): void
    {
        $this->dropIndexIfPresent('special_prices', 'special_prices_user_product_index');
        $this->dropIndexIfPresent('group_discounts', 'group_discounts_user_category_lookup');
        $this->dropIndexIfPresent('products', 'products_catalog_visibility_index');
    }

    private function addIndexIfMissing(string $table, array $columns, string $name): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndexIfPresent(string $table, string $name): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropIndex($name);
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => $index->name === $name);
        }

        $database = DB::getDatabaseName();
        return (int) (DB::selectOne(
            'select count(1) as count from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ?',
            [$database, $table, $name]
        )->count ?? 0) > 0;
    }
};
