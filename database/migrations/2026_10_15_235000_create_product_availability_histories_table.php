<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_availability_histories')) {
            return;
        }

        Schema::create('product_availability_histories', function (Blueprint $table): void {
            $table->id();
            // Legacy deployments can use different integer widths for users/products.
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('changed_by_user_id')->nullable()->index();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_availability_histories');
    }
};
