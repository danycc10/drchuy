<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 24)->default('editor');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->unsignedInteger('position');
            $table->timestamps();
        });
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('source_id')->unique();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('service_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->json('payload');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('current_revision_id')->nullable()->constrained('service_revisions')->nullOnDelete();
            $table->foreignId('published_revision_id')->nullable()->constrained('service_revisions')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
        });
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_revision_id');
            $table->dropConstrainedForeignId('published_revision_id');
            $table->dropColumn('published_at');
        });
        Schema::dropIfExists('service_revisions');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'is_active']));
    }
};
