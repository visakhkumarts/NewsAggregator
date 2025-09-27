<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Article extends Model
{
    use HasFactory;

    public const DEFAULT_VIEW_COUNT = 0;
    public const MAX_TITLE_LENGTH = 255;
    public const MAX_DESCRIPTION_LENGTH = 1000;
    public const MAX_CONTENT_LENGTH = 10000;

    protected $fillable = [
        'news_source_id',
        'category_id',
        'external_id',
        'title',
        'description',
        'content',
        'url',
        'image_url',
        'author',
        'published_at',
        'metadata',
        'view_count',
        'is_featured',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the news source that owns the article.
     */
    public function newsSource(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class);
    }

    /**
     * Get the category that owns the article.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include featured articles.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to order by published date.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('published_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to search articles by title and description.
     * Optimized for better performance with proper indexing.
     */
    public function scopeSearch(Builder $query, string $searchTerm): Builder
    {
        $searchTerm = trim($searchTerm);
        
        if (empty($searchTerm)) {
            return $query;
        }
        
        return $query->where(function (Builder $q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%")
              ->orWhere('content', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope a query to filter by news source.
     */
    public function scopeBySource(Builder $query, int $sourceId): Builder
    {
        return $query->where('news_source_id', $sourceId);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to filter by author.
     */
    public function scopeByAuthor(Builder $query, string $author): Builder
    {
        return $query->where('author', 'like', "%{$author}%");
    }

    /**
     * Increment the view count for the article.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Get the formatted published date.
     */
    public function getFormattedPublishedAtAttribute(): string
    {
        return $this->published_at->format('M d, Y');
    }

    /**
     * Get the time ago string for published date.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->published_at->diffForHumans();
    }

    /**
     * Check if the article is recent (published within last 24 hours).
     */
    public function isRecent(): bool
    {
        return $this->published_at->isAfter(now()->subDay());
    }

    /**
     * Check if the article has an image.
     */
    public function hasImage(): bool
    {
        return !empty($this->image_url);
    }

    /**
     * Get a truncated version of the content.
     */
    public function getExcerpt(int $length = 150): string
    {
        if (empty($this->content)) {
            return $this->description ?? '';
        }

        return strlen($this->content) > $length 
            ? substr($this->content, 0, $length) . '...'
            : $this->content;
    }

    /**
     * Mark article as featured.
     */
    public function markAsFeatured(): void
    {
        $this->update(['is_featured' => true]);
    }

    /**
     * Unmark article as featured.
     */
    public function unmarkAsFeatured(): void
    {
        $this->update(['is_featured' => false]);
    }

    /**
     * Set the title attribute with proper formatting.
     */
    public function setTitleAttribute(string $value): void
    {
        $this->attributes['title'] = trim($value);
    }

    /**
     * Set the description attribute with proper formatting.
     */
    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['description'] = $value ? trim($value) : null;
    }

    /**
     * Set the content attribute with proper formatting.
     */
    public function setContentAttribute(?string $value): void
    {
        $this->attributes['content'] = $value ? trim($value) : null;
    }

    /**
     * Set the author attribute with proper formatting.
     */
    public function setAuthorAttribute(?string $value): void
    {
        $this->attributes['author'] = $value ? trim($value) : null;
    }

    /**
     * Get the reading time estimate in minutes.
     */
    public function getReadingTimeAttribute(): int
    {
        if (empty($this->content)) {
            return 0;
        }

        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // Average reading speed: 200 words per minute
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Article $article) {
            if (empty($article->view_count)) {
                $article->view_count = self::DEFAULT_VIEW_COUNT;
            }
        });

        static::saving(function (Article $article) {
            if (empty($article->external_id) && !empty($article->url)) {
                $article->external_id = md5($article->url);
            }
        });
    }
}
