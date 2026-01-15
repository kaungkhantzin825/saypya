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
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail && str_starts_with($this->thumbnail, 'http')) {
            return $this->thumbnail;
        }
        if ($this->thumbnail && file_exists(storage_path('app/public/' . $this->thumbnail))) {
            return asset('storage/' . $this->thumbnail);
        }
        // Return placeholder image based on course title
        $colors = ['3498db', 'e74c3c', '2ecc71', '9b59b6', 'f39c12', '1abc9c', 'e67e22', '34495e'];
        $colorIndex = crc32($this->title ?? 'course') % count($colors);
        $color = $colors[$colorIndex];
        return "https://placehold.co/400x300/{$color}/ffffff?text=" . urlencode(substr($this->title ?? 'Course', 0, 20));
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
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    public function getTotalStudentsAttribute()
    {
        return $this->enrollments()->where('payment_status', 'completed')->count();
    }

    public function getTotalLessonsAttribute()
    {
        return $this->lessons()->count();
    }

    public function getTotalDurationAttribute()
    {
        return $this->lessons()->sum('video_duration');
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