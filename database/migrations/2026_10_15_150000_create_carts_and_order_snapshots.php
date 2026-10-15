<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->timestamp('last_activity_at')->nullable()->index();
                $table->string('session_migration_hash', 64)->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('carts', 'session_migration_hash')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->string('session_migration_hash', 64)->nullable();
            });
        }

        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cart_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedInteger('quantity');
                $table->timestamps();

                $table->unique(['cart_id', 'product_id'], 'cart_items_cart_product_unique');
                $table->index('product_id');
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->nullable()->unique();
            }
            if (!Schema::hasColumn('orders', 'subtotal_net')) {
                $table->bigInteger('subtotal_net')->default(0);
            }
            if (!Schema::hasColumn('orders', 'vat_total')) {
                $table->bigInteger('vat_total')->default(0);
            }
            if (!Schema::hasColumn('orders', 'gross_total')) {
                $table->bigInteger('gross_total')->default(0);
            }
        });

        Schema::table('items', function (Blueprint $table) {
            $columns = [
                'product_title' => fn () => $table->string('product_title')->nullable(),
                'serial_number' => fn () => $table->string('serial_number')->nullable(),
                'unit_measure' => fn () => $table->string('unit_measure')->nullable(),
                'pack_quantity' => fn () => $table->unsignedInteger('pack_quantity')->nullable(),
                'line_net' => fn () => $table->bigInteger('line_net')->nullable(),
                'vat_amount' => fn () => $table->bigInteger('vat_amount')->nullable(),
                'vat_rate' => fn () => $table->string('vat_rate')->nullable(),
            ];

            foreach ($columns as $name => $add) {
                if (!Schema::hasColumn('items', $name)) {
                    $add();
                }
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                foreach (['product_title', 'serial_number', 'unit_measure', 'pack_quantity', 'line_net', 'vat_amount', 'vat_rate'] as $column) {
                    if (Schema::hasColumn('items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                foreach (['order_number', 'subtotal_net', 'vat_total', 'gross_total'] as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'session_migration_hash')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropColumn('session_migration_hash');
            });
        }

        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
