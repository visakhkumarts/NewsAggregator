<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;

class UserPreference extends Model
{
    use HasFactory;

    public const DEFAULT_LANGUAGE = 'en';
    public const DEFAULT_COUNTRY = 'us';
    public const DEFAULT_ARTICLES_PER_PAGE = 20;
    public const DEFAULT_REFRESH_INTERVAL = 300;
    public const MIN_REFRESH_INTERVAL = 60;
    public const MAX_REFRESH_INTERVAL = 3600;

    protected $fillable = [
        'user_id',
        'preferred_sources',
        'preferred_categories',
        'preferred_authors',
        'language',
        'country',
        'articles_per_page',
        'show_images',
        'auto_refresh',
        'refresh_interval',
    ];

    protected $casts = [
        'preferred_sources' => 'array',
        'preferred_categories' => 'array',
        'preferred_authors' => 'array',
        'articles_per_page' => 'integer',
        'show_images' => 'boolean',
        'auto_refresh' => 'boolean',
        'refresh_interval' => 'integer',
    ];

    /**
     * Get the user that owns the preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the preferred news sources.
     */
    public function preferredNewsSources(): Collection
    {
        if (!$this->preferred_sources) {
            return collect();
        }

        return NewsSource::whereIn('id', $this->preferred_sources)->get();
    }

    /**
     * Get the preferred categories.
     */
    public function preferredCategories(): Collection
    {
        if (!$this->preferred_categories) {
            return collect();
        }

        return Category::whereIn('id', $this->preferred_categories)->get();
    }

    /**
     * Add a preferred source.
     */
    public function addPreferredSource(int $sourceId): void
    {
        $sources = $this->preferred_sources ?? [];
        if (!in_array($sourceId, $sources)) {
            $sources[] = $sourceId;
            $this->preferred_sources = $sources;
            $this->save();
        }
    }

    /**
     * Remove a preferred source.
     */
    public function removePreferredSource(int $sourceId): void
    {
        $sources = $this->preferred_sources ?? [];
        $this->preferred_sources = array_values(array_filter($sources, fn($id) => $id !== $sourceId));
        $this->save();
    }

    /**
     * Add a preferred category.
     */
    public function addPreferredCategory(int $categoryId): void
    {
        $categories = $this->preferred_categories ?? [];
        if (!in_array($categoryId, $categories)) {
            $categories[] = $categoryId;
            $this->preferred_categories = $categories;
            $this->save();
        }
    }

    /**
     * Remove a preferred category.
     */
    public function removePreferredCategory(int $categoryId): void
    {
        $categories = $this->preferred_categories ?? [];
        $this->preferred_categories = array_values(array_filter($categories, fn($id) => $id !== $categoryId));
        $this->save();
    }

    /**
     * Check if refresh interval is valid.
     */
    public function isValidRefreshInterval(): bool
    {
        return $this->refresh_interval >= self::MIN_REFRESH_INTERVAL 
            && $this->refresh_interval <= self::MAX_REFRESH_INTERVAL;
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (UserPreference $preference) {
            if (empty($preference->language)) {
                $preference->language = self::DEFAULT_LANGUAGE;
            }
            if (empty($preference->country)) {
                $preference->country = self::DEFAULT_COUNTRY;
            }
            if (empty($preference->articles_per_page)) {
                $preference->articles_per_page = self::DEFAULT_ARTICLES_PER_PAGE;
            }
            if (empty($preference->refresh_interval)) {
                $preference->refresh_interval = self::DEFAULT_REFRESH_INTERVAL;
            }
        });
    }
}
