<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('ticket_number')->unique();
            // The deployed legacy users table can use a different integer width.
            // Ownership is enforced by TicketService, so keep this indexed without a cross-table FK.
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('type', 40)->default('general')->index();
            $table->string('priority', 20)->default('normal')->index();
            $table->string('status', 30)->default('new')->index();
            $table->string('subject');
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->timestamp('last_reply_at')->nullable()->index();
            $table->timestamp('last_customer_reply_at')->nullable();
            $table->timestamp('last_admin_reply_at')->nullable();
            $table->timestamp('response_due_at')->nullable()->index();
            $table->timestamp('resolution_due_at')->nullable()->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('ticket_messages')) {
            Schema::create('ticket_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('author_user_id')->nullable()->index();
            $table->text('body');
            $table->boolean('is_internal')->default(false)->index();
            $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        if (! Schema::hasTable('ticket_attachments')) {
            Schema::create('ticket_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_message_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable()->index();
            $table->string('disk', 40)->default('private');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->string('sha256', 64)->nullable();
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('ticket_status_histories')) {
            Schema::create('ticket_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('changed_by_user_id')->nullable()->index();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        if (! Schema::hasTable('ticket_reads')) {
            Schema::create('ticket_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamp('last_read_at');
            $table->timestamps();
            $table->unique(['ticket_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_reads');
        Schema::dropIfExists('ticket_status_histories');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
    }
};
