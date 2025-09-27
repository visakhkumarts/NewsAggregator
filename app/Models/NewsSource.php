<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class NewsSource extends Model
{
    use HasFactory;

    public const API_PROVIDER_NEWSAPI = 'newsapi';
    public const API_PROVIDER_GUARDIAN = 'guardian';
    public const API_PROVIDER_NYTIMES = 'nytimes';

    public const DEFAULT_PRIORITY = 0;
    public const MAX_PRIORITY = 100;

    protected $fillable = [
        'name',
        'slug',
        'api_provider',
        'api_endpoint',
        'api_config',
        'is_active',
        'priority',
        'description',
        'logo_url',
        'website_url',
    ];

    protected $casts = [
        'api_config' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get the articles for the news source.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Scope a query to only include active sources.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by priority.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc')->orderBy('name');
    }

    /**
     * Check if the news source is available.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && !empty($this->api_endpoint);
    }

    /**
     * Get the API configuration for this source.
     */
    public function getApiConfig(string $key = null): mixed
    {
        if ($key === null) {
            return $this->api_config;
        }

        return $this->api_config[$key] ?? null;
    }

    /**
     * Set the API configuration for this source.
     */
    public function setApiConfig(string $key, mixed $value): void
    {
        $config = $this->api_config ?? [];
        $config[$key] = $value;
        $this->api_config = $config;
    }

    /**
     * Get the total number of articles from this source.
     */
    public function getArticleCount(): int
    {
        return $this->articles()->count();
    }

    /**
     * Set the name attribute with proper formatting.
     */
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = trim($value);
    }

    /**
     * Set the slug attribute with proper formatting.
     */
    public function setSlugAttribute(string $value): void
    {
        $this->attributes['slug'] = strtolower(trim($value));
    }

    /**
     * Set the description attribute with proper formatting.
     */
    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['description'] = $value ? trim($value) : null;
    }

    /**
     * Get the display name for the news source.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->is_active ? '' : ' (Inactive)');
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (NewsSource $newsSource) {
            if (empty($newsSource->priority)) {
                $newsSource->priority = self::DEFAULT_PRIORITY;
            }
        });
    }
}
