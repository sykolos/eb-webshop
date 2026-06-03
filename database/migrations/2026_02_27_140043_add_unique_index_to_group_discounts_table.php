<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $indexName = 'group_discounts_user_category_unique';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('group_discounts') || $this->indexExists()) {
            return;
        }

        Schema::table('group_discounts', function (Blueprint $table) {
            $table->unique(['user_id', 'category_id'], $this->indexName);
        });
    }

    public function down()
    {
        if (!Schema::hasTable('group_discounts') || !$this->indexExists()) {
            return;
        }

        Schema::table('group_discounts', function (Blueprint $table) {
            $table->dropUnique($this->indexName);
        });
    }

    private function indexExists(): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::selectOne(
            'select count(1) as count from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ?',
            [$database, 'group_discounts', $this->indexName]
        );

        return (int) ($result->count ?? 0) > 0;
    }
};
