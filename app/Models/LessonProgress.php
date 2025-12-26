<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'is_completed',
        'watch_time',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    // Helper methods
    public function markAsCompleted()
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->save();

        // Update course enrollment progress
        $enrollment = Enrollment::where('user_id', $this->user_id)
            ->whereHas('course.lessons', function ($query) {
                $query->where('lessons.id', $this->lesson_id);
            })
            ->first();

        if ($enrollment) {
            $enrollment->updateProgress();
        }
    }

    public function getProgressPercentageAttribute()
    {
        if (!$this->lesson->video_duration || $this->lesson->video_duration === 0) {
            return $this->is_completed ? 100 : 0;
        }

        return min(100, round(($this->watch_time / $this->lesson->video_duration) * 100));
    }
}