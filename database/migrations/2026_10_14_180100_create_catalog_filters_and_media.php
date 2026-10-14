<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_media')) {
            Schema::create('product_media', function (Blueprint $table): void {
                $table->id();
                // A legacy products.id típusa telepítésenként eltérhet; az alkalmazás kezeli a kapcsolatot.
                $table->unsignedBigInteger('product_id');
                $table->string('kind', 20)->default('image');
                $table->string('disk', 20)->default('public');
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_public')->default(true);
                $table->timestamps();
                $table->index(['product_id', 'kind', 'sort_order']);
            });
        }

        if (Schema::hasTable('products')) {
            DB::table('products')
                ->whereNotNull('image')
                ->where('image', '<>', '')
                ->orderBy('id')
                ->get(['id', 'image'])
                ->each(function (object $product): void {
                    $alreadyImported = DB::table('product_media')
                        ->where('product_id', $product->id)
                        ->where('path', $product->image)
                        ->exists();

                    if (!$alreadyImported) {
                        DB::table('product_media')->insert([
                            'product_id' => $product->id,
                            'kind' => 'image',
                            'disk' => 'public',
                            'path' => $product->image,
                            'original_name' => basename($product->image),
                            'mime_type' => 'image/*',
                            'size' => null,
                            'sort_order' => 0,
                            'is_public' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                });
        }

        if (!Schema::hasTable('filter_definitions')) {
            Schema::create('filter_definitions', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type', 30)->default('select');
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('filter_values')) {
            Schema::create('filter_values', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('filter_definition_id');
                $table->string('label');
                $table->string('slug');
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['filter_definition_id', 'slug']);
            });
        }

        if (!Schema::hasTable('category_filter_definition')) {
            Schema::create('category_filter_definition', function (Blueprint $table): void {
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('filter_definition_id');
                $table->primary(['category_id', 'filter_definition_id']);
            });
        }

        if (!Schema::hasTable('product_filter_value')) {
            Schema::create('product_filter_value', function (Blueprint $table): void {
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('filter_value_id');
                $table->primary(['product_id', 'filter_value_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_filter_value');
        Schema::dropIfExists('category_filter_definition');
        Schema::dropIfExists('filter_values');
        Schema::dropIfExists('filter_definitions');
        Schema::dropIfExists('product_media');
    }
};
