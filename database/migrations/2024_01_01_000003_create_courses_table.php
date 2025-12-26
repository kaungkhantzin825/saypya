<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->string('thumbnail');
            $table->string('preview_video')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advanced']);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('requirements')->nullable();
            $table->json('what_you_learn')->nullable();
            $table->string('language')->default('English');
            $table->integer('duration_hours')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};