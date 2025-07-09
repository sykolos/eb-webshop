<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('items', function (Blueprint $table) {
        if (!Schema::hasColumn('items', 'order_id')) {
            $table->unsignedBigInteger('order_id')->after('product_id');
        }

        if (!Schema::hasColumn('items', 'unit_price')) {
            $table->unsignedInteger('unit_price')->after('quantity')->nullable();
        }

        if (Schema::hasColumn('items', 'color_id')) {
            $table->dropColumn('color_id');
        }
    });
}

public function down()
{
    Schema::table('items', function (Blueprint $table) {
        if (Schema::hasColumn('items', 'order_id')) {
            $table->dropColumn('order_id');
        }

        if (Schema::hasColumn('items', 'unit_price')) {
            $table->dropColumn('unit_price');
        }

        $table->unsignedBigInteger('color_id')->nullable(); // csak ha vissza kellene
    });
}

};
