<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->timestamp('last_seen_at')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('carts', 'converted_at')) {
            Schema::table('carts', function (Blueprint $table): void {
                $table->timestamp('converted_at')->nullable()->index();
            });
        }

        if (!Schema::hasTable('activity_events')) {
            Schema::create('activity_events', function (Blueprint $table): void {
                $table->id();
                $table->uuid('event_uuid')->unique();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_hash', 64)->nullable()->index();
                $table->string('event_name', 40)->index();
                $table->string('page_path')->nullable();
                $table->string('referrer_host')->nullable();
                $table->string('campaign_source')->nullable();
                $table->string('campaign_medium')->nullable();
                $table->string('campaign_name')->nullable();
                $table->string('device_type', 20)->nullable()->index();
                $table->json('payload')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
                $table->index(['event_name', 'occurred_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_events');

        if (Schema::hasColumn('carts', 'converted_at')) {
            Schema::table('carts', fn (Blueprint $table) => $table->dropColumn('converted_at'));
        }
        if (Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('last_seen_at'));
        }
    }
};
