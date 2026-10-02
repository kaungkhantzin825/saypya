<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'avatar',
        'bio',
        'phone',
        'date_of_birth',
        'gender',
        'country',
        'is_active',
        'last_login_at',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Accessors the Inertia/Vue layer reads. Eloquent does not serialise
     * accessors by default, so without this every avatar renders empty.
     */
    protected $appends = [
        'avatar_url',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'is_super_admin' => 'boolean',
        'password' => 'hashed',
    ];

    // Relationships
    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
                    ->withPivot(['price_paid', 'payment_status', 'enrolled_at', 'progress_percentage'])
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlist()
    {
        return $this->belongsToMany(Course::class, 'wishlists')->withTimestamps();
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }

    public function discussionReplies()
    {
        return $this->hasMany(DiscussionReply::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    // Scopes
    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    public function scopeLecturers($query)
    {
        return $query->where('role', 'lecturer');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isLecturer()
    {
        return $this->role === 'lecturer';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin()
    {
        return (bool) $this->is_super_admin;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    public function hasEnrolled($courseId)
    {
        return $this->enrollments()->where('course_id', $courseId)->exists();
    }

    /**
     * Absolute URL of the avatar, or `null` when the user has not uploaded one.
     *
     * This used to return a `https://ui-avatars.com/...` URL. That made every
     * avatar an external request that leaked the user's name to a third party,
     * and rendered as a broken image whenever the host was unreachable. `null`
     * lets `Avatar.vue` show its initials fallback, which is the app's existing
     * treatment for a missing avatar everywhere else.
     */
    public function getAvatarUrlAttribute()
    {
        // If already a full URL (starts with http/https)
        if ($this->avatar && str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        // If avatar exists, build a request-relative URL so it works on any host/port.
        if ($this->avatar) {
            $path = ltrim($this->avatar, '/');

            return str_starts_with($path, 'storage/')
                ? asset($path)
                : asset('storage/' . $path);
        }

        return null;
    }
}