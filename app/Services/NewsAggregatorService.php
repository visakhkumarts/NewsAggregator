<?php

namespace App\Services;

use App\Models\NewsSource;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NewsAggregatorService
{
    protected NewsServiceFactory $serviceFactory;

    public function __construct(NewsServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * Fetch and store articles from all active news sources.
     */
    public function aggregateNews(array $options = []): array
    {
        $results = [];
        $activeSources = NewsSource::active()->ordered()->get();

        // Filter sources if specified
        if (isset($options['sources']) && !empty($options['sources'])) {
            $activeSources = $activeSources->whereIn('api_provider', $options['sources']);
        }

        foreach ($activeSources as $newsSource) {
            try {
                $service = $this->serviceFactory->create($newsSource);
                
                if (!$service || !$service->isAvailable()) {
                    Log::warning("Skipping unavailable service: {$newsSource->name}");
                    continue;
                }

                // Prepare specific options for each service using the already created service
                $serviceOptions = $this->prepareServiceOptions($options, $service);
                $articles = $service->fetchArticles($serviceOptions);
                $storedCount = $this->storeArticles($articles, $newsSource);

                $results[$newsSource->name] = [
                    'fetched' => count($articles),
                    'stored' => $storedCount,
                    'status' => 'success'
                ];

                Log::info("Aggregated news from {$newsSource->name}", [
                    'fetched' => count($articles),
                    'stored' => $storedCount
                ]);

            } catch (\Exception $e) {
                Log::error("Failed to aggregate news from {$newsSource->name}", [
                    'message' => $e->getMessage(),
                    'news_source_id' => $newsSource->id
                ]);

                $results[$newsSource->name] = [
                    'fetched' => 0,
                    'stored' => 0,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Prepare service-specific options for each news source.
     * Now delegates to each service to handle its own options preparation.
     */
    protected function prepareServiceOptions(array $options, \App\Contracts\NewsServiceInterface $service): array
    {
        $serviceOptions = $options;

        // Remove sources parameter as it's used for filtering services, not API parameters
        unset($serviceOptions['sources']);

        // Let the service prepare its own options
        return $service->prepareOptions($serviceOptions);
    }

    /**
     * Store articles in the database.
     */
    protected function storeArticles(array $articles, NewsSource $newsSource): int
    {
        $storedCount = 0;

        foreach ($articles as $articleData) {
            try {
                $article = $this->createArticleFromData($articleData, $newsSource);
                if ($article) {
                    $storedCount++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to store article", [
                    'message' => $e->getMessage(),
                    'article_data' => $articleData,
                    'news_source_id' => $newsSource->id
                ]);
            }
        }

        // Clear relevant caches when new articles are stored
        if ($storedCount > 0) {
            $this->clearArticleCaches();
        }

        return $storedCount;
    }

    /**
     * Create an article from API data.
     */
    protected function createArticleFromData(array $data, NewsSource $newsSource): ?Article
    {
        // Validate required fields
        if (empty($data['title']) || empty($data['url'])) {
            Log::warning("Skipping article with missing required fields", [
                'data' => $data,
                'news_source_id' => $newsSource->id
            ]);
            return null;
        }

        // Check if article already exists
        if (Article::where('url', $data['url'])->exists()) {
            return null;
        }

        // Find or create category
        $category = $this->findOrCreateCategory($data['category'] ?? null);

        return Article::create([
            'news_source_id' => $newsSource->id,
            'category_id' => $category?->id,
            'external_id' => $data['external_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
            'url' => $data['url'],
            'image_url' => $data['image_url'] ?? null,
            'author' => $data['author'] ?? null,
            'published_at' => $this->parseDate($data['published_at'] ?? null),
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Find or create a category.
     */
    protected function findOrCreateCategory(?string $categoryName): ?Category
    {
        if (empty($categoryName)) {
            return null;
        }

        return Category::firstOrCreate(
            ['slug' => Str::slug($categoryName)],
            [
                'name' => $categoryName,
                'is_active' => true
            ]
        );
    }

    /**
     * Parse published date from various formats.
     */
    protected function parseDate(?string $dateString): \Carbon\Carbon
    {
        if (empty($dateString)) {
            return \Carbon\Carbon::now();
        }

        try {
            return \Carbon\Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$dateString}");
            return \Carbon\Carbon::now();
        }
    }

    /**
     * Get articles with filtering and pagination.
     */
    public function getArticles(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = Article::with(['newsSource', 'category']);

        // Validate category_id exists if provided
        if (isset($filters['category_id'])) {
            $categoryExists = Category::where('id', $filters['category_id'])->exists();
            if (!$categoryExists) {
                return [
                    'data' => [],
                    'pagination' => [
                        'current_page' => $page,
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'has_more' => false,
                    ]
                ];
            }
        }

        // Validate source_id exists if provided
        if (isset($filters['source_id'])) {
            $sourceExists = NewsSource::where('id', $filters['source_id'])->exists();
            if (!$sourceExists) {
                return [
                    'data' => [],
                    'pagination' => [
                        'current_page' => $page,
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'has_more' => false,
                    ]
                ];
            }
        }

        // Apply filters
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (isset($filters['source_id'])) {
            $query->bySource($filters['source_id']);
        }

        if (isset($filters['author'])) {
            $query->byAuthor($filters['author']);
        }

        // Handle date filtering - support both date_from only and date range
        if (isset($filters['date_from'])) {
            if (isset($filters['date_to'])) {
                // Both dates provided - use date range
                $query->dateRange($filters['date_from'], $filters['date_to']);
            } else {
                // Only date_from provided - filter from that date onwards
                $query->where('published_at', '>=', $filters['date_from']);
            }
        } elseif (isset($filters['date_to'])) {
            // Only date_to provided - filter up to that date
            $query->where('published_at', '<=', $filters['date_to']);
        }

        // Handle featured filtering - support both true and false values
        if (isset($filters['featured'])) {
            if ($filters['featured'] === true || $filters['featured'] === 'true' || $filters['featured'] === '1') {
                $query->featured();
            } elseif ($filters['featured'] === false || $filters['featured'] === 'false' || $filters['featured'] === '0') {
                $query->where('is_featured', false);
            }
        }

        // Apply ordering after all filters
        $query->latest('published_at');

        $articles = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $articles->items(),
            'pagination' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'has_more' => $articles->hasMorePages(),
            ]
        ];
    }

    /**
     * Get personalized articles based on user preferences.
     */
    public function getPersonalizedArticles(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = Article::with(['newsSource', 'category']);

        // Apply standard filters
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        // Handle date filtering - support both date_from only and date range
        if (isset($filters['date_from'])) {
            if (isset($filters['date_to'])) {
                // Both dates provided - use date range
                $query->dateRange($filters['date_from'], $filters['date_to']);
            } else {
                // Only date_from provided - filter from that date onwards
                $query->where('published_at', '>=', $filters['date_from']);
            }
        } elseif (isset($filters['date_to'])) {
            // Only date_to provided - filter up to that date
            $query->where('published_at', '<=', $filters['date_to']);
        }

        // Handle featured filtering - support both true and false values
        if (isset($filters['featured'])) {
            if ($filters['featured'] === true || $filters['featured'] === 'true' || $filters['featured'] === '1') {
                $query->featured();
            } elseif ($filters['featured'] === false || $filters['featured'] === 'false' || $filters['featured'] === '0') {
                $query->where('is_featured', false);
            }
        }

        // Apply user preference filters
        if (isset($filters['preferred_sources']) && !empty($filters['preferred_sources'])) {
            $query->whereIn('news_source_id', $filters['preferred_sources']);
        }

        if (isset($filters['preferred_categories']) && !empty($filters['preferred_categories'])) {
            $query->whereIn('category_id', $filters['preferred_categories']);
        }

        if (isset($filters['preferred_authors']) && !empty($filters['preferred_authors'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['preferred_authors'] as $author) {
                    $q->orWhere('author', 'like', "%{$author}%");
                }
            });
        }

        // Apply ordering after all filters
        $query->latest('published_at');

        $articles = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $articles->items(),
            'pagination' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'has_more' => $articles->hasMorePages(),
            ]
        ];
    }

    /**
     * Get featured articles with intelligent caching.
     * 
     * @param int $limit Maximum number of articles to return
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedArticles(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        // Validate limit to prevent abuse
        $limit = min(max($limit, 1), 100);
        
        $cacheKey = $this->generateCacheKey('featured_articles', ['limit' => $limit]);
        $ttl = config('cache_ttl.featured_articles', 300); // 5 minutes default
        
        try {
            return Cache::tags(['articles', 'featured'])
                ->remember($cacheKey, $ttl, function () use ($limit) {
                    return $this->fetchFeaturedArticlesFromDatabase($limit);
                });
        } catch (\Exception $e) {
            // Log cache error and fallback to database
            Log::warning('Cache error for featured articles, falling back to database', [
                'error' => $e->getMessage(),
                'limit' => $limit
            ]);
            
            return $this->fetchFeaturedArticlesFromDatabase($limit);
        }
    }

    /**
     * Fetch featured articles directly from database.
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function fetchFeaturedArticlesFromDatabase(int $limit): \Illuminate\Database\Eloquent\Collection
    {
        return Article::with(['newsSource:id,name,slug,logo_url', 'category:id,name,slug,color'])
            ->featured()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Generate consistent cache keys.
     * 
     * @param string $prefix
     * @param array $params
     * @return string
     */
    public function generateCacheKey(string $prefix, array $params = []): string
    {
        $key = "news_aggregator:{$prefix}";
        
        if (!empty($params)) {
            ksort($params); // Ensure consistent key generation
            $key .= ':' . md5(serialize($params));
        }
        
        return $key;
    }

    /**
     * Get articles by category with caching.
     */
    public function getArticlesByCategory(int $categoryId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "articles_category_{$categoryId}_{$limit}";
        
        return Cache::remember($cacheKey, 180, function () use ($categoryId, $limit) {
            return Article::with(['newsSource:id,name,slug,logo_url', 'category:id,name,slug,color'])
                ->byCategory($categoryId)
                ->latest()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get articles by source with caching.
     */
    public function getArticlesBySource(int $sourceId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "articles_source_{$sourceId}_{$limit}";
        
        return Cache::remember($cacheKey, 180, function () use ($sourceId, $limit) {
            return Article::with(['newsSource:id,name,slug,logo_url', 'category:id,name,slug,color'])
                ->bySource($sourceId)
                ->latest()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get statistics about the news aggregation.
     */
    public function getStatistics(): array
    {
        $cacheKey = 'news_statistics';
        
        return Cache::remember($cacheKey, 300, function () {
            return [
                'total_articles' => Article::count(),
                'articles_today' => Article::whereDate('created_at', today())->count(),
                'articles_this_week' => Article::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'total_sources' => NewsSource::active()->count(),
                'total_categories' => Category::active()->count(),
                'most_active_source' => $this->getMostActiveSource(),
                'most_popular_category' => $this->getMostPopularCategory(),
            ];
        });
    }

    /**
     * Get the most active news source by article count.
     */
    protected function getMostActiveSource(): ?array
    {
        $source = NewsSource::withCount('articles')
            ->orderBy('articles_count', 'desc')
            ->first();

        return $source ? [
            'id' => $source->id,
            'name' => $source->name,
            'articles_count' => $source->articles_count
        ] : null;
    }

    /**
     * Get the most popular category by article count.
     */
    protected function getMostPopularCategory(): ?array
    {
        $category = Category::withCount('articles')
            ->orderBy('articles_count', 'desc')
            ->first();

        return $category ? [
            'id' => $category->id,
            'name' => $category->name,
            'articles_count' => $category->articles_count
        ] : null;
    }

    /**
     * Clear article-related caches using tags for better performance.
     */
    public function clearArticleCaches(): void
    {
        try {
            // Clear all article-related caches using tags
            Cache::tags(['articles', 'featured'])->flush();
            Cache::tags(['articles', 'categories'])->flush();
            Cache::tags(['articles', 'sources'])->flush();
            Cache::tags(['statistics'])->flush();
            
            Log::info('Article caches cleared successfully');
        } catch (\Exception $e) {
            // Fallback to manual cache clearing if tags are not supported
            Log::warning('Cache tags not supported, using manual cache clearing', [
                'error' => $e->getMessage()
            ]);
            
            $this->clearArticleCachesManually();
        }
    }

    /**
     * Fallback method to clear caches manually when tags are not supported.
     */
    protected function clearArticleCachesManually(): void
    {
        $cacheKeys = [
            'news_statistics',
        ];

        // Clear featured articles caches
        for ($limit = 1; $limit <= 100; $limit++) {
            $cacheKeys[] = $this->generateCacheKey('featured_articles', ['limit' => $limit]);
        }

        // Clear category and source specific caches
        $categories = Category::pluck('id');
        $sources = NewsSource::pluck('id');

        foreach ($categories as $categoryId) {
            for ($limit = 1; $limit <= 100; $limit++) {
                $cacheKeys[] = $this->generateCacheKey('category_articles', ['category_id' => $categoryId, 'limit' => $limit]);
            }
        }

        foreach ($sources as $sourceId) {
            for ($limit = 1; $limit <= 100; $limit++) {
                $cacheKeys[] = $this->generateCacheKey('source_articles', ['source_id' => $sourceId, 'limit' => $limit]);
            }
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
