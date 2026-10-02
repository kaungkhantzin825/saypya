<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'status',
        'published_at',
        'views_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = ['image_url', 'featured_image_url', 'reading_time'];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /**
     * Get the author of the blog post
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope for published posts
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope for draft posts
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get the featured image URL
     *
     * Returns null when there is no image. It used to fall back to
     * `https://via.placeholder.com/...`, which no longer resolves — every blog
     * page then logged a failed request. All consumers already guard with
     * `v-if="post.image_url"`, so null renders the neutral empty state instead.
     */
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return asset('storage/' . $this->featured_image);
        }

        return null;
    }

    /**
     * Alias of `featured_image_url` — the Vue blog pages read `image_url`.
     */
    public function getImageUrlAttribute()
    {
        return $this->featured_image_url;
    }

    /**
     * Rough reading time in minutes, at ~200 words per minute.
     */
    public function getReadingTimeAttribute()
    {
        $words = str_word_count(strip_tags($this->content ?? ''));

        return max(1, (int) ceil($words / 200));
    }

    /**
     * Body HTML for display.
     *
     * Posts are written as **plain text, one line per paragraph, with no markup at
     * all**. Piping that straight into `v-html` collapses every newline, so a
     * 65-line article reached the page as one solid wall of text with no paragraph
     * breaks — the author's structure was silently thrown away.
     *
     * Bare lines are wrapped in `<p>` and escaped. If the body already contains
     * block markup (someone used a rich editor), it is returned untouched so the
     * existing HTML keeps working.
     *
     * Read `content_html` for display; `content` stays the raw source for the editor.
     *
     * Deliberately **not** in `$appends`: the list endpoints already ship the full
     * raw `content` for every row, and appending the rendered HTML too would double
     * that payload on the blog index and the admin list for no benefit. The show
     * action appends it explicitly instead.
     */
    public function getContentHtmlAttribute(): string
    {
        $content = trim((string) ($this->content ?? ''));

        if ($content === '') {
            return '';
        }

        if (preg_match('#<(p|div|section|h[1-6]|ul|ol|li|blockquote|pre|table|figure|img|br)\b#i', $content)) {
            return $content;
        }

        $lines = preg_split('/\R/u', $content) ?: [];

        $paragraphs = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $paragraphs[] = '<p>' . e($line) . '</p>';
            }
        }

        return implode("\n", $paragraphs);
    }

    /**
     * Get the excerpt or generate from content
     */
    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }
        return Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Increment views count
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }
}
