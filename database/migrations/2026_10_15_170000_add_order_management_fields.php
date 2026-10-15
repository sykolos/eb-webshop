<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('orders', 'status_responsible_id')) {
                $table->unsignedBigInteger('status_responsible_id')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'created_by_admin_id')) {
                $table->unsignedBigInteger('created_by_admin_id')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'admin_idempotency_key')) {
                $table->uuid('admin_idempotency_key')->nullable()->unique();
            }
            if (!Schema::hasColumn('orders', 'shipping_receiver')) {
                $table->string('shipping_receiver')->nullable();
            }
            if (!Schema::hasColumn('orders', 'status_note')) {
                $table->text('status_note')->nullable();
            }
            if (!Schema::hasColumn('orders', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->index();
            }
        });

        if (!Schema::hasTable('order_status_histories')) {
            Schema::create('order_status_histories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->unsignedBigInteger('responsible_id')->nullable()->index();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');

        Schema::table('orders', function (Blueprint $table): void {
            foreach ([
                'status_responsible_id',
                'created_by_admin_id',
                'admin_idempotency_key',
                'shipping_receiver',
                'status_note',
                'deleted_at',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
