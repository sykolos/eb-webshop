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
    Schema::table('orders', function (Blueprint $table) {
        $table->unsignedBigInteger('shipping_address_id')->nullable()->after('user_id');

        $table->foreign('shipping_address_id')
              ->references('id')
              ->on('user_shippings')
              ->onDelete('set null'); // vagy 'cascade', ha szeretnéd hogy törléskor törlődjön
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropForeign(['shipping_address_id']);
        $table->dropColumn('shipping_address_id');
    });
}
};
