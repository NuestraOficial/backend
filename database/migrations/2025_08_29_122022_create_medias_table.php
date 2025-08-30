<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('folder_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('moment_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['image', 'video']);
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->date('date')->nullable();
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};
