<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_runs', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20);
            $table->string('filename')->nullable();
            $table->unsignedBigInteger('bytes')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('restore_checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('backup_runs'); }
};
