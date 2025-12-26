<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'sort_order',
        'course_id',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    // Accessors
    public function getTotalLessonsAttribute()
    {
        return $this->lessons()->count();
    }

    public function getTotalDurationAttribute()
    {
        return $this->lessons()->sum('video_duration');
    }
}