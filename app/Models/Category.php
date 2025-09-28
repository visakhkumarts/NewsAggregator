<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;

    public const MAX_NAME_LENGTH = 100;
    public const MAX_DESCRIPTION_LENGTH = 500;
    public const MAX_COLOR_LENGTH = 7; // #RRGGBB format
    public const DEFAULT_COLOR = '#3B82F6';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Validation rules for the model.
     */
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:' . self::MAX_NAME_LENGTH,
            'slug' => 'required|string|max:100|unique:categories,slug',
            'description' => 'nullable|string|max:' . self::MAX_DESCRIPTION_LENGTH,
            'color' => 'nullable|string|max:' . self::MAX_COLOR_LENGTH,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the articles for the category.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
     * Set the color attribute with proper formatting.
     */
    public function setColorAttribute(?string $value): void
    {
        $this->attributes['color'] = $value ? strtoupper(trim($value)) : self::DEFAULT_COLOR;
    }

    /**
     * Get the display name for the category.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->is_active ? '' : ' (Inactive)');
    }

    /**
     * Check if the category has articles.
     */
    public function hasArticles(): bool
    {
        return $this->articles()->exists();
    }

    /**
     * Get the article count for this category.
     */
    public function getArticleCount(): int
    {
        return $this->articles()->count();
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Category $category) {
            if (empty($category->color)) {
                $category->color = self::DEFAULT_COLOR;
            }
        });
    }
}
