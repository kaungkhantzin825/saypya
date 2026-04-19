<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    // ── Helper: recalculate progress and save to enrollments table ────────────
    private function recalculateProgress(int $userId, int $lessonId): int
    {
        // Get course_id directly via section_id (no model relation chain)
        $courseId = DB::table('sections')
            ->join('lessons', 'sections.id', '=', 'lessons.section_id')
            ->where('lessons.id', $lessonId)
            ->value('sections.course_id');

        if (!$courseId) return 0;

        // Total lessons in this course
        $totalLessons = DB::table('lessons')
            ->join('sections', 'sections.id', '=', 'lessons.section_id')
            ->where('sections.course_id', $courseId)
            ->count();

        if ($totalLessons === 0) return 0;

        // How many the student completed
        $completedCount = DB::table('lesson_progress')
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('sections', 'sections.id', '=', 'lessons.section_id')
            ->where('sections.course_id', $courseId)
            ->where('lesson_progress.user_id', $userId)
            ->where('lesson_progress.is_completed', 1)
            ->count();

        $pct = (int) round(($completedCount / $totalLessons) * 100);

        // Save to enrollments table
        DB::table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->update([
                'progress_percentage' => $pct,
                'updated_at'          => now(),
            ]);

        return $pct;
    }

    // ── Mark lesson complete ──────────────────────────────────────────────────
    public function markComplete(Lesson $lesson)
    {
        $userId = Auth::id();

        // Upsert lesson_progress record
        DB::table('lesson_progress')->updateOrInsert(
            ['user_id' => $userId, 'lesson_id' => $lesson->id],
            [
                'is_completed' => 1,
                'completed_at' => now(),
                'updated_at'   => now(),
                'created_at'   => now(),
            ]
        );

        $pct = $this->recalculateProgress($userId, $lesson->id);

        return response()->json([
            'success'             => true,
            'message'             => 'Lesson marked as completed!',
            'progress_percentage' => $pct,
        ]);
    }

    // ── Mark lesson incomplete (uncomplete / undo) ────────────────────────────
    public function markUncomplete(Lesson $lesson)
    {
        $userId = Auth::id();

        DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->where('lesson_id', $lesson->id)
            ->update([
                'is_completed' => 0,
                'completed_at' => null,
                'updated_at'   => now(),
            ]);

        $pct = $this->recalculateProgress($userId, $lesson->id);

        return response()->json([
            'success'             => true,
            'message'             => 'Lesson marked as incomplete.',
            'progress_percentage' => $pct,
        ]);
    }

    // ── Update progress (video watch time) ───────────────────────────────────
    public function updateProgress(Request $request, Lesson $lesson)
    {
        $userId = Auth::id();

        $request->validate([
            'watch_time'   => 'required|integer|min:0',
            'is_completed' => 'boolean',
        ]);

        DB::table('lesson_progress')->updateOrInsert(
            ['user_id' => $userId, 'lesson_id' => $lesson->id],
            [
                'watch_time'   => $request->watch_time,
                'is_completed' => $request->is_completed ?? false,
                'updated_at'   => now(),
                'created_at'   => now(),
            ]
        );

        if ($request->is_completed) {
            $pct = $this->recalculateProgress($userId, $lesson->id);
        } else {
            $pct = 0;
        }

        return response()->json([
            'success'             => true,
            'progress_percentage' => $pct,
        ]);
    }
}