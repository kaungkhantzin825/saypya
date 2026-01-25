<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
        'description',
        'duration_minutes',
        'passing_score',
        'max_attempts',
        'show_results',
        'show_correct_answers',
        'is_published',
    ];

    protected $casts = [
        'show_results' => 'boolean',
        'show_correct_answers' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function getTotalPointsAttribute()
    {
        return $this->questions->sum('points');
    }

    public function userAttempts($userId)
    {
        return $this->attempts()->where('user_id', $userId)->count();
    }

    public function canUserAttempt($userId)
    {
        return $this->userAttempts($userId) < $this->max_attempts;
    }
}
