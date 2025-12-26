<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'course_id',
        'lesson_id',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
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

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function replies()
    {
        return $this->hasMany(DiscussionReply::class)->orderBy('created_at');
    }

    // Scopes
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function markAsResolved()
    {
        $this->is_resolved = true;
        $this->save();
    }

    public function markAsUnresolved()
    {
        $this->is_resolved = false;
        $this->save();
    }

    public function getTotalRepliesAttribute()
    {
        return $this->replies()->count();
    }
}