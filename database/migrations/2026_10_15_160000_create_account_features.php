<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'account_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('account_status')->default('active')->index();
            });
        }

        if (!Schema::hasColumn('user_shippings', 'is_default')) {
            Schema::table('user_shippings', function (Blueprint $table) {
                $table->boolean('is_default')->default(false)->index();
            });
        }

        Schema::create('partner_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('reviewed_by')->nullable()->index();
            $table->string('name');
            $table->string('email')->index();
            $table->string('company_name');
            $table->string('vatnumber')->nullable();
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
            $table->index('product_id');
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('order_updates')->default(true);
            $table->boolean('newsletter_subscribed')->default(false);
            $table->boolean('marketing_notifications')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('user_favorites');
        Schema::dropIfExists('partner_registration_requests');

        if (Schema::hasColumn('user_shippings', 'is_default')) {
            Schema::table('user_shippings', fn (Blueprint $table) => $table->dropColumn('is_default'));
        }

        if (Schema::hasColumn('users', 'account_status')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('account_status'));
        }
    }
};
