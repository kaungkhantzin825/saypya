<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'video_url',
        'video_duration',
        'content',
        'attachments',
        'is_preview',
        'sort_order',
        'section_id',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_preview' => 'boolean',
    ];

    // Relationships
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function course()
    {
        return $this->hasOneThrough(Course::class, Section::class, 'id', 'id', 'section_id', 'course_id');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }

    // Scopes
    public function scopePreview($query)
    {
        return $query->where('is_preview', true);
    }

    public function scopeVideo($query)
    {
        return $query->where('type', 'video');
    }

    // Accessors
    public function getVideoUrlFullAttribute()
    {
        return $this->video_url ? asset('storage/' . $this->video_url) : null;
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->video_duration) return '0:00';
        
        $minutes = floor($this->video_duration / 60);
        $seconds = $this->video_duration % 60;
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    // Helper methods
    public function isCompletedBy($userId)
    {
        return $this->progress()
                    ->where('user_id', $userId)
                    ->where('is_completed', true)
                    ->exists();
    }

    public function getProgressFor($userId)
    {
        return $this->progress()
                    ->where('user_id', $userId)
                    ->first();
    }
}