<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                if (!Schema::hasColumn('products', 'slug')) {
                    $table->string('slug')->nullable()->index();
                }
                if (!Schema::hasColumn('products', 'manufacturer')) {
                    $table->string('manufacturer')->nullable()->index();
                }
                if (!Schema::hasColumn('products', 'is_published')) {
                    $table->boolean('is_published')->default(true)->index();
                }
                if (!Schema::hasColumn('products', 'availability_status')) {
                    $table->string('availability_status', 40)->default('in_stock')->index();
                }
                if (!Schema::hasColumn('products', 'is_archived')) {
                    $table->boolean('is_archived')->default(false)->index();
                }
                if (!Schema::hasColumn('products', 'specifications')) {
                    $table->json('specifications')->nullable();
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table): void {
                if (!Schema::hasColumn('categories', 'slug')) {
                    $table->string('slug')->nullable()->index();
                }
                if (!Schema::hasColumn('categories', 'parent_id')) {
                    $table->unsignedInteger('parent_id')->nullable()->index();
                }
                if (!Schema::hasColumn('categories', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->index();
                }
                if (!Schema::hasColumn('categories', 'is_active')) {
                    $table->boolean('is_active')->default(true)->index();
                }
            });
        }

        if (Schema::hasTable('product_units')) {
            Schema::table('product_units', function (Blueprint $table): void {
                if (!Schema::hasColumn('product_units', 'slug')) {
                    $table->string('slug')->nullable()->index();
                }
                if (!Schema::hasColumn('product_units', 'is_active')) {
                    $table->boolean('is_active')->default(true)->index();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'products' => ['slug', 'manufacturer', 'is_published', 'availability_status', 'is_archived', 'specifications'],
            'categories' => ['slug', 'parent_id', 'sort_order', 'is_active'],
            'product_units' => ['slug', 'is_active'],
        ] as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
