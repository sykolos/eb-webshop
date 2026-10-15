<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('event', 40)->index();
            $table->boolean('enabled')->default(false)->index();
            $table->unsignedInteger('delay_minutes')->default(0);
            $table->time('window_start')->nullable();
            $table->time('window_end')->nullable();
            $table->string('timezone', 64)->default('Europe/Budapest');
            $table->unsignedInteger('max_per_user')->default(1);
            $table->unsignedInteger('cooldown_minutes')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->unsignedBigInteger('created_by_admin_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('automation_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rule_id')->nullable()->constrained('automation_rules')->nullOnDelete();
            $table->string('idempotency_key')->unique();
            $table->string('event', 40)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('cart_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('subject_id')->nullable();
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('queued')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });

        DB::table('automation_rules')->insert([
            ['key' => 'abandoned_cart', 'name' => 'Elhagyott kosár emlékeztető', 'event' => 'cart_abandoned', 'enabled' => false, 'delay_minutes' => 1440, 'timezone' => 'Europe/Budapest', 'max_per_user' => 1, 'cooldown_minutes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'partner_approved', 'name' => 'Partnerjóváhagyás értesítés', 'event' => 'partner_approved', 'enabled' => false, 'delay_minutes' => 0, 'timezone' => 'Europe/Budapest', 'max_per_user' => 1, 'cooldown_minutes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'order_status_changed', 'name' => 'Rendelésállapot értesítés', 'event' => 'order_status_changed', 'enabled' => false, 'delay_minutes' => 0, 'timezone' => 'Europe/Budapest', 'max_per_user' => 1, 'cooldown_minutes' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'ticket_update', 'name' => 'Ticket frissítés értesítés', 'event' => 'ticket_update', 'enabled' => false, 'delay_minutes' => 0, 'timezone' => 'Europe/Budapest', 'max_per_user' => 1, 'cooldown_minutes' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_runs');
        Schema::dropIfExists('automation_rules');
    }
};
