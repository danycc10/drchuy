<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('original_name');
            $table->string('original_mime', 40);
            $table->string('directory')->unique();
            $table->string('extension', 8);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedInteger('display_width');
            $table->unsignedInteger('display_height');
            $table->unsignedBigInteger('original_bytes');
            $table->unsignedBigInteger('display_bytes');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('content_revision_media', function (Blueprint $table) {
            $table->foreignId('content_revision_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained()->restrictOnDelete();
            $table->primary(['content_revision_id', 'media_asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_revision_media');
        Schema::dropIfExists('media_assets');
    }
};
