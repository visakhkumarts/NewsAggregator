<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleIndexRequest;
use App\Http\Requests\ArticleSearchRequest;
use App\Http\Resources\ApiResponseResource;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\NewsSource;
use App\Services\NewsAggregatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected NewsAggregatorService $newsAggregatorService;

    public function __construct(NewsAggregatorService $newsAggregatorService)
    {
        $this->newsAggregatorService = $newsAggregatorService;
    }

    
    public function index(ArticleIndexRequest $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'category_id', 'source_id', 'author', 
            'date_from', 'date_to', 'featured'
        ]);

        // Convert featured string to boolean
        if (isset($filters['featured'])) {
            $filters['featured'] = filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        $result = $this->newsAggregatorService->getArticles($filters, $page, $perPage);

        // Check if no results found
        if (empty($result['data']) && $result['pagination']['total'] === 0) {
            $message = $this->buildNoResultsMessage($filters);
            return ApiResponseResource::success([
                'data' => [],
                'pagination' => $result['pagination'],
                'message' => $message,
                'filters_applied' => $this->getAppliedFilters($filters)
            ], $message);
        }

        return ApiResponseResource::success([
            'data' => ArticleResource::collection($result['data']),
            'pagination' => $result['pagination']
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $article = Article::with(['newsSource', 'category'])->find($id);

        if (!$article) {
            return ApiResponseResource::notFound('Article not found');
        }

        $article->incrementViewCount();

        return ApiResponseResource::success(new ArticleResource($article));
    }

    /**
     * Get featured articles.
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'limit' => 'nullable|integer|min:1|max:50'
            ]);
            
            $limit = $request->get('limit', 5);
            $articles = $this->newsAggregatorService->getFeaturedArticles($limit);

            // Check if no featured articles found
            if ($articles->isEmpty()) {
                return ApiResponseResource::success([
                    'data' => [],
                    'message' => 'No featured articles found.'
                ], 'No featured articles found.');
            }

            return ApiResponseResource::success(ArticleResource::collection($articles));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseResource::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to get featured articles', [
                'error' => $e->getMessage(),
                'limit' => $request->get('limit')
            ]);
            
            return ApiResponseResource::error('Failed to retrieve featured articles', null, 500);
        }
    }

    /**
     * Get articles by category.
     */
    public function byCategory(Request $request, int $categoryId): JsonResponse
    {
        try {
            $request->validate([
                'limit' => 'nullable|integer|min:1|max:100'
            ]);
            
            $category = Category::find($categoryId);
            
            if (!$category) {
                return ApiResponseResource::notFound('Category not found');
            }

            $limit = $request->get('limit', 20);
            $articles = $this->newsAggregatorService->getArticlesByCategory($categoryId, $limit);

            // Check if no articles found for this category
            if ($articles->isEmpty()) {
                return ApiResponseResource::success([
                    'data' => [],
                    'category' => new \App\Http\Resources\CategoryResource($category),
                    'message' => "No articles found in category '{$category->name}'."
                ], "No articles found in category '{$category->name}'.");
            }

            return ApiResponseResource::success([
                'data' => ArticleResource::collection($articles),
                'category' => new \App\Http\Resources\CategoryResource($category)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseResource::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to get articles by category', [
                'error' => $e->getMessage(),
                'category_id' => $categoryId,
                'limit' => $request->get('limit')
            ]);
            
            return ApiResponseResource::error('Failed to retrieve articles for this category', null, 500);
        }
    }

    /**
     * Get articles by source.
     */
    public function bySource(Request $request, int $sourceId): JsonResponse
    {
        try {
            $request->validate([
                'limit' => 'nullable|integer|min:1|max:100'
            ]);
            
            $source = NewsSource::find($sourceId);
            
            if (!$source) {
                return ApiResponseResource::notFound('News source not found');
            }

            $limit = $request->get('limit', 20);
            $articles = $this->newsAggregatorService->getArticlesBySource($sourceId, $limit);

            return ApiResponseResource::success([
                'data' => ArticleResource::collection($articles),
                'source' => new \App\Http\Resources\NewsSourceResource($source)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseResource::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to get articles by source', [
                'error' => $e->getMessage(),
                'source_id' => $sourceId,
                'limit' => $request->get('limit')
            ]);
            
            return ApiResponseResource::error('Failed to retrieve articles for this source', null, 500);
        }
    }

    public function search(ArticleSearchRequest $request): JsonResponse
    {
        $filters = ['search' => $request->get('q')];
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        $result = $this->newsAggregatorService->getArticles($filters, $page, $perPage);

        // Check if no results found
        if (empty($result['data']) && $result['pagination']['total'] === 0) {
            $query = $request->get('q');
            $message = "No articles found for search term '{$query}'.";
            
            return ApiResponseResource::success([
                'data' => [],
                'pagination' => $result['pagination'],
                'query' => $query,
                'message' => $message
            ], $message);
        }

        return ApiResponseResource::success([
            'data' => ArticleResource::collection($result['data']),
            'pagination' => $result['pagination'],
            'query' => $request->get('q')
        ]);
    }

    /**
     * Get latest articles.
     */
    public function latest(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'limit' => 'nullable|integer|min:1|max:100'
            ]);
            
            $limit = $request->get('limit', 20);
            
            $articles = Article::with(['newsSource:id,name,slug,logo_url', 'category:id,name,slug,color'])
                ->select(['id', 'title', 'description', 'url', 'image_url', 'author', 'published_at', 'view_count', 'is_featured', 'news_source_id', 'category_id', 'created_at'])
                ->latest('published_at')
                ->limit($limit)
                ->get();

            return ApiResponseResource::success(ArticleResource::collection($articles));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseResource::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to get latest articles', [
                'error' => $e->getMessage(),
                'limit' => $request->get('limit')
            ]);
            
            return ApiResponseResource::error('Failed to retrieve latest articles', null, 500);
        }
    }

    /**
     * Build a descriptive message when no results are found.
     */
    protected function buildNoResultsMessage(array $filters): string
    {
        $conditions = [];
        
        if (isset($filters['search'])) {
            $conditions[] = "search term '{$filters['search']}'";
        }
        
        if (isset($filters['category_id'])) {
            $category = \App\Models\Category::find($filters['category_id']);
            $categoryName = $category ? $category->name : "category ID {$filters['category_id']}";
            $conditions[] = "category '{$categoryName}'";
        }
        
        if (isset($filters['source_id'])) {
            $source = \App\Models\NewsSource::find($filters['source_id']);
            $sourceName = $source ? $source->name : "source ID {$filters['source_id']}";
            $conditions[] = "source '{$sourceName}'";
        }
        
        if (isset($filters['author'])) {
            $conditions[] = "author '{$filters['author']}'";
        }
        
        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $conditions[] = "date range from {$filters['date_from']} to {$filters['date_to']}";
        }
        
        if (isset($filters['featured'])) {
            $featuredText = $filters['featured'] ? 'featured' : 'non-featured';
            $conditions[] = "{$featuredText} articles";
        }
        
        if (empty($conditions)) {
            return "No articles found in the database.";
        }
        
        return "No articles found matching " . implode(', ', $conditions) . ".";
    }

    /**
     * Get a summary of applied filters for debugging.
     */
    protected function getAppliedFilters(array $filters): array
    {
        $applied = [];
        
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $applied[$key] = $value;
            }
        }
        
        return $applied;
    }
}
