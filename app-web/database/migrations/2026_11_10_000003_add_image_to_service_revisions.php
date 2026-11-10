<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_revisions', function (Blueprint $table) {
            $table->foreignId('image_id')->nullable()->constrained('media_assets')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_revisions', fn (Blueprint $table) => $table->dropConstrainedForeignId('image_id'));
    }
};
