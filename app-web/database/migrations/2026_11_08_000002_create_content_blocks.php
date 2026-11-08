<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->timestamps();
        });
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_block_id')->constrained()->cascadeOnDelete();
            $table->json('payload');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->foreignId('current_revision_id')->nullable()->constrained('content_revisions')->nullOnDelete();
            $table->foreignId('published_revision_id')->nullable()->constrained('content_revisions')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
        });
        Schema::table('audit_events', function (Blueprint $table) {
            $table->foreignId('content_block_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('audit_events', fn (Blueprint $table) => $table->dropConstrainedForeignId('content_block_id'));
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_revision_id');
            $table->dropConstrainedForeignId('published_revision_id');
            $table->dropColumn('published_at');
        });
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('content_blocks');
    }
};
