<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'discussion_id',
        'is_instructor_reply',
    ];

    protected $casts = [
        'is_instructor_reply' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    // Scopes
    public function scopeInstructorReplies($query)
    {
        return $query->where('is_instructor_reply', true);
    }

    public function scopeStudentReplies($query)
    {
        return $query->where('is_instructor_reply', false);
    }
}