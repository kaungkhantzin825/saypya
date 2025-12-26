<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['video', 'text', 'quiz', 'assignment']);
            $table->string('video_url')->nullable();
            $table->integer('video_duration')->nullable(); // in seconds
            $table->text('content')->nullable();
            $table->json('attachments')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->integer('sort_order')->default(0);
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};