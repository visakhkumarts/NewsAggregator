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

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        $result = $this->newsAggregatorService->getArticles($filters, $page, $perPage);

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
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:50'
        ]);
        
        $limit = $request->get('limit', 5);
        $articles = $this->newsAggregatorService->getFeaturedArticles($limit);

        return ApiResponseResource::success(ArticleResource::collection($articles));
    }

    /**
     * Get articles by category.
     */
    public function byCategory(Request $request, int $categoryId): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100'
        ]);
        
        $category = Category::find($categoryId);
        
        if (!$category) {
            return ApiResponseResource::notFound('Category not found');
        }

        $limit = $request->get('limit', 20);
        $articles = $this->newsAggregatorService->getArticlesByCategory($categoryId, $limit);

        return ApiResponseResource::success([
            'data' => ArticleResource::collection($articles),
            'category' => new \App\Http\Resources\CategoryResource($category)
        ]);
    }

    /**
     * Get articles by source.
     */
    public function bySource(Request $request, int $sourceId): JsonResponse
    {
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
    }

    public function search(ArticleSearchRequest $request): JsonResponse
    {
        $filters = ['search' => $request->get('q')];
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        $result = $this->newsAggregatorService->getArticles($filters, $page, $perPage);

        return ApiResponseResource::success([
            'data' => ArticleResource::collection($result['data']),
            'pagination' => $result['pagination'],
            'query' => $request->get('q')
        ]);
    }

    public function latest(Request $request): JsonResponse
    {
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
    }
}
