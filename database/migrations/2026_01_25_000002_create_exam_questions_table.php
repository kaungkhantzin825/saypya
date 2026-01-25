<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->text('question');
            $table->enum('type', ['multiple_choice', 'essay', 'true_false']);
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->json('options')->nullable(); // For multiple choice: ["Option 1", "Option 2", ...]
            $table->string('correct_answer')->nullable(); // For MCQ: "0", "1", etc. For True/False: "true" or "false"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
