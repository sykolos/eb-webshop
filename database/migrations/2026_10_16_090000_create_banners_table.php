<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table): void {
            $table->id();
            $table->string('slot', 30)->default('hero')->index();
            $table->string('title')->nullable();
            $table->string('alt_text');
            $table->string('desktop_path')->nullable();
            $table->string('tablet_path')->nullable();
            $table->string('mobile_path')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_label', 120)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            // Legacy deployments use different users.id widths, so this is an indexed audit value without an FK.
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
