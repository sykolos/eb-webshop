<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('offers')) {
            Schema::create('offers', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('summary')->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_published')->default(false)->index();
                $table->timestamp('starts_at')->nullable()->index();
                $table->timestamp('ends_at')->nullable()->index();
                // Legacy deployments use different users.id widths, so this is an indexed audit value without an FK.
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('offer_product')) {
            Schema::create('offer_product', function (Blueprint $table): void {
                $table->unsignedBigInteger('offer_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->unique(['offer_id', 'product_id']);
            });
        }

        if (! Schema::hasTable('offer_category')) {
            Schema::create('offer_category', function (Blueprint $table): void {
                $table->unsignedBigInteger('offer_id')->index();
                $table->unsignedBigInteger('category_id')->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->unique(['offer_id', 'category_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_product');
        Schema::dropIfExists('offer_category');
        Schema::dropIfExists('offers');
    }
};
