<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the cascade delete issue on courses table.
     * Previously, courses were automatically deleted when:
     * 1. Their category was deleted
     * 2. Their instructor (user) was deleted
     * 
     * Now changed to RESTRICT to prevent accidental deletions.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign(['category_id']);
            $table->dropForeign(['instructor_id']);
            
            // Re-add foreign keys with RESTRICT (prevents deletion if courses exist)
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('restrict'); // Cannot delete category if it has courses
            
            $table->foreign('instructor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict'); // Cannot delete user if they have courses
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Drop restricted foreign keys
            $table->dropForeign(['category_id']);
            $table->dropForeign(['instructor_id']);
            
            // Restore original cascade delete behavior
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');
            
            $table->foreign('instructor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
