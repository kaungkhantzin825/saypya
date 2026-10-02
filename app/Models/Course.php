<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'thumbnail',
        'preview_video',
        'price',
        'discount_price',
        'level',
        'status',
        'requirements',
        'what_you_learn',
        'language',
        'duration_hours',
        'is_featured',
        'category_id',
        'instructor_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'requirements' => 'array',
        'what_you_learn' => 'array',
        'is_featured' => 'boolean',
    ];

    /**
     * Computed attributes the Inertia/Vue frontend reads directly.
     * Every accessor below prefers an already-loaded relation so that eager-loaded
     * listings don't turn into an N+1.
     */
    protected $appends = [
        'thumbnail_url',
        'preview_video_url',
        'current_price',
        'discount_percentage',
        'average_rating',
        'total_reviews',
        'total_students',
        'total_lessons',
        'total_duration',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Section::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments')
                    ->withPivot(['price_paid', 'payment_status', 'enrolled_at', 'progress_percentage'])
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->parentOnly()->approved()->with(['user', 'replies'])->latest();
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Accessors
    /**
     * Absolute URL of the course thumbnail, or `null` when the course has none.
     *
     * This used to return a `https://placehold.co/...` URL, which meant a course
     * without a thumbnail depended on a third-party host. Returning `null` keeps
     * "no thumbnail" honest in the data layer — the admin form can then tell an
     * empty thumbnail apart from a real one — and the views render the brand mark
     * via `AppImage.vue`.
     */
    public function getThumbnailUrlAttribute()
    {
        // If already a full URL (starts with http/https)
        if ($this->thumbnail && str_starts_with($this->thumbnail, 'http')) {
            return $this->thumbnail;
        }

        // Build the URL relative to the current request, so it works on any
        // host/port. Using config('app.url') pinned every image to APP_URL and
        // broke previews served from a different port.
        if ($this->thumbnail) {
            $path = ltrim($this->thumbnail, '/');

            return str_starts_with($path, 'storage/')
                ? asset($path)
                : asset('storage/' . $path);
        }

        return null;
    }

    public function getPreviewVideoUrlAttribute()
    {
        return $this->preview_video ? asset('storage/' . $this->preview_video) : null;
    }

    public function getCurrentPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->discount_price && $this->price > 0) {
            return round((($this->price - $this->discount_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getAverageRatingAttribute()
    {
        $value = $this->relationLoaded('reviews')
            ? $this->reviews->avg('rating')
            : $this->reviews()->avg('rating');

        return round((float) $value, 1);
    }

    public function getTotalReviewsAttribute()
    {
        return $this->relationLoaded('reviews')
            ? $this->reviews->count()
            : $this->reviews()->count();
    }

    public function getTotalStudentsAttribute()
    {
        if ($this->relationLoaded('enrollments')) {
            return $this->enrollments->where('payment_status', 'completed')->count();
        }

        return $this->enrollments()->where('payment_status', 'completed')->count();
    }

    public function getTotalLessonsAttribute()
    {
        // Set by `withCount('lessons')` on listings.
        if (array_key_exists('lessons_count', $this->attributes)) {
            return (int) $this->attributes['lessons_count'];
        }

        if ($this->relationLoaded('lessons')) {
            return $this->lessons->count();
        }

        return $this->lessons()->count();
    }

    public function getTotalDurationAttribute()
    {
        if ($this->relationLoaded('lessons')) {
            return (int) $this->lessons->sum('video_duration');
        }

        return (int) $this->lessons()->sum('video_duration');
    }

    // Helper methods
    public function isFree()
    {
        return $this->current_price == 0;
    }

    public function hasDiscount()
    {
        return $this->discount_price && $this->discount_price < $this->price;
    }

    public function isEnrolledBy($userId)
    {
        return $this->enrollments()
                    ->where('user_id', $userId)
                    ->where('payment_status', 'completed')
                    ->exists();
    }

    public function isInWishlistOf($userId)
    {
        return $this->wishlists()->where('user_id', $userId)->exists();
    }
}