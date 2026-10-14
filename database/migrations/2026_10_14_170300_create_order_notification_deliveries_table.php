<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Az örökölt orders.id mező stagingen nem minden telepítésen azonos
        // MySQL-típusú. Az értesítés-idempotencia nem igényel FK-kényszert,
        // a hivatkozás indexelve marad és a meglévő félkész táblát megőrizzük.
        if (Schema::hasTable('order_notification_deliveries')) {
            return;
        }

        Schema::create('order_notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id')->index();
            $table->string('notification_type');
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'notification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notification_deliveries');
    }
};
