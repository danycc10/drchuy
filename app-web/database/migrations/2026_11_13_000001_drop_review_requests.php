<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// El panel publica en un solo paso; la bandeja de solicitudes de revisión dejó de existir.
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('review_requests');
    }

    public function down(): void
    {
        Schema::create('review_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('content_block_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('revision_id');
            $table->unsignedBigInteger('base_revision_id')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->text('note')->nullable();
            $table->text('response')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['service_id', 'revision_id']);
            $table->unique(['content_block_id', 'revision_id']);
        });
    }
};
