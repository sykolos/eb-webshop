<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('banners') && ! Schema::hasColumn('banners', 'offer_id')) {
            Schema::table('banners', function (Blueprint $table): void {
                $table->unsignedBigInteger('offer_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('banners') && Schema::hasColumn('banners', 'offer_id')) {
            Schema::table('banners', function (Blueprint $table): void {
                $table->dropColumn('offer_id');
            });
        }
    }
};
