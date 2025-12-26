<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function updateProgress(Request $request, Lesson $lesson)
    {
        $user = Auth::user();
        
        $request->validate([
            'watch_time' => 'required|integer|min:0',
            'is_completed' => 'boolean'
        ]);

        $progress = LessonProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'watch_time' => $request->watch_time,
                'is_completed' => $request->is_completed ?? false,
            ]
        );

        if ($request->is_completed) {
            $progress->markAsCompleted();
        }

        return response()->json([
            'success' => true,
            'completed' => $progress->is_completed,
            'progress_percentage' => $progress->progress_percentage,
        ]);
    }

    public function markComplete(Lesson $lesson)
    {
        $user = Auth::user();
        
        $progress = LessonProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'is_completed' => true,
                'completed_at' => now(),
            ]
        );

        $progress->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Lesson marked as completed!'
        ]);
    }
}