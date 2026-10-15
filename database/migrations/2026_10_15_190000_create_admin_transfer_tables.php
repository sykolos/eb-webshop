<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin_exports')) {
            Schema::create('admin_exports', function (Blueprint $table): void {
                $table->id();
                // The legacy users table has deployment-specific integer sizing.
                // Keep the audit owner nullable without imposing a new FK on it.
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('dataset', 60);
                $table->string('format', 10);
                $table->string('download_token', 80)->unique();
                $table->json('filters')->nullable();
                $table->string('status', 20)->default('queued')->index();
                $table->string('file_path')->nullable();
                $table->string('file_name')->nullable();
                $table->unsignedInteger('rows_count')->default(0);
                $table->text('error')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_imports')) {
            Schema::create('admin_imports', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('dataset', 60)->default('products');
                $table->string('original_name');
                $table->string('checksum', 64)->index();
                $table->string('status', 20)->default('preview')->index();
                $table->longText('preview_payload');
                $table->json('summary')->nullable();
                $table->text('error')->nullable();
                $table->timestamp('applied_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_imports');
        Schema::dropIfExists('admin_exports');
    }
};
