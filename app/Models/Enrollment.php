<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'price_paid',
        'payment_status',
        'payment_method',
        'transaction_id',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
        'last_accessed_at',
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('enrolled_at', 'desc');
    }

    // Helper methods
    public function isCompleted()
    {
        return $this->payment_status === 'completed';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function isFailed()
    {
        return $this->payment_status === 'failed';
    }

    public function isRefunded()
    {
        return $this->payment_status === 'refunded';
    }

    public function updateProgress()
    {
        $totalLessons = $this->course->lessons()->count();
        if ($totalLessons === 0) {
            $this->progress_percentage = 0;
            return;
        }

        $completedLessons = LessonProgress::where('user_id', $this->user_id)
            ->whereIn('lesson_id', $this->course->lessons()->pluck('id'))
            ->where('is_completed', true)
            ->count();

        $this->progress_percentage = round(($completedLessons / $totalLessons) * 100);
        
        if ($this->progress_percentage >= 100 && !$this->completed_at) {
            $this->completed_at = now();
        }
        
        $this->save();
    }
}