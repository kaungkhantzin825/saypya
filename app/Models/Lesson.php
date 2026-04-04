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
        if (!$this->video_url) return null;
        
        // Check if it's a YouTube URL
        if ($this->isYoutubeUrl($this->video_url)) {
            return $this->video_url;
        }
        
        // Check if it's already a full URL (starts with http:// or https://)
        if (preg_match('/^https?:\/\//', $this->video_url)) {
            return $this->video_url;
        }
        
        // Otherwise, treat as local storage file
        return asset('storage/' . $this->video_url);
    }
    
    public function getYoutubeEmbedUrlAttribute()
    {
        if (!$this->video_url) return null;
        
        // Extract YouTube video ID and convert to embed URL
        $videoId = $this->extractYoutubeId($this->video_url);
        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
    }
    
    public function isYoutubeUrl($url)
    {
        return preg_match('/(?:youtube\.com|youtu\.be)/', $url);
    }
    
    private function extractYoutubeId($url)
    {
        // Handle various YouTube URL formats
        $patterns = [
            '/youtube\.com\/watch\?v=([^&]+)/',
            '/youtube\.com\/embed\/([^?]+)/',
            '/youtu\.be\/([^?]+)/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
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