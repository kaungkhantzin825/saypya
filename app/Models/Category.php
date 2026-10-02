<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Eloquent does not serialise accessors unless they are appended. The Vue
     * admin reads both of these directly.
     */
    protected $appends = [
        'image_url',
        'courses_count',
    ];

    // Relationships
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Accessors

    /**
     * True when `image` points at a file on the public disk, as opposed to a
     * full external URL. Only local files may be deleted from storage.
     */
    public function hasLocalImage(): bool
    {
        return filled($this->image) && ! preg_match('#^https?://#i', $this->image);
    }

    /**
     * `categories.image` holds either a full external URL (legacy rows seeded
     * from Unsplash) or a path on the public disk. Prepending `storage/` blindly
     * produced `storage/https://…` and broke every image, so branch on the shape
     * of the value. Returns null when there is no image, so callers can render
     * their own placeholder instead of a 404.
     */
    public function getImageUrlAttribute()
    {
        if (blank($this->image)) {
            return null;
        }

        if (preg_match('#^https?://#i', $this->image)) {
            return $this->image;
        }

        $path = ltrim($this->image, '/');

        return str_starts_with($path, 'storage/') ? asset($path) : asset('storage/' . $path);
    }

    public function getCoursesCountAttribute()
    {
        // Prefer a count already loaded via withCount() so appending this does
        // not turn every listing into N+1 queries. The controller supplies it as
        // `withCount(['courses as courses_count' => ...published()])` to keep the
        // published-only semantics below.
        if (array_key_exists('courses_count', $this->attributes)) {
            return (int) $this->attributes['courses_count'];
        }

        return $this->courses()->published()->count();
    }
}