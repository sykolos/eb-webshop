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
        Schema::table('group_discounts', function (Blueprint $table) {
            $table->unique(['user_id', 'category_id'], 'group_discounts_user_category_unique');
        });
    }

    public function down()
    {
        Schema::table('group_discounts', function (Blueprint $table) {
            $table->dropUnique('group_discounts_user_category_unique');
        });
    }
};
