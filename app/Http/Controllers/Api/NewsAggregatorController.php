<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsAggregationRequest;
use App\Http\Resources\ApiResponseResource;
use App\Services\NewsAggregatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NewsAggregatorController extends Controller
{
    protected NewsAggregatorService $newsAggregatorService;

    public function __construct(NewsAggregatorService $newsAggregatorService)
    {
        $this->newsAggregatorService = $newsAggregatorService;
    }

    /**
     * Trigger news aggregation from all sources.
     */
    public function aggregate(NewsAggregationRequest $request): JsonResponse
    {
        $options = $request->only(['sources', 'categories', 'limit']);

        try {
            $results = $this->newsAggregatorService->aggregateNews($options);

            return ApiResponseResource::success($results, 'News aggregation completed');
        } catch (\Exception $e) {
            Log::error('News aggregation failed', [
                'error' => $e->getMessage(),
                'options' => $options
            ]);

            return ApiResponseResource::error('News aggregation failed', null, 500);
        }
    }

    /**
     * Get aggregation statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->newsAggregatorService->getStatistics();

            // Check if no statistics available
            if (empty($stats) || (isset($stats['total_articles']) && $stats['total_articles'] === 0)) {
                $message = "No statistics available. No articles have been aggregated yet.";
                
                return ApiResponseResource::success([
                    'data' => $stats ?: [],
                    'message' => $message
                ], $message);
            }

            return ApiResponseResource::success($stats);
        } catch (\Exception $e) {
            Log::error('Failed to get statistics', [
                'error' => $e->getMessage()
            ]);

            return ApiResponseResource::error('Failed to get statistics', null, 500);
        }
    }

    /**
     * Get dashboard data.
     */
    public function dashboard(): JsonResponse
    {
        try {
            $stats = $this->newsAggregatorService->getStatistics();
            $featuredArticles = $this->newsAggregatorService->getFeaturedArticles(5);
            $latestArticles = $this->newsAggregatorService->getArticles([], 1, 10);

            // Check if no dashboard data available
            $hasData = !empty($featuredArticles) || !empty($latestArticles['data']) || 
                      (isset($stats['total_articles']) && $stats['total_articles'] > 0);

            if (!$hasData) {
                $message = "No dashboard data available. No articles have been aggregated yet.";
                
                return ApiResponseResource::success([
                    'statistics' => $stats ?: [],
                    'featured_articles' => [],
                    'latest_articles' => [],
                    'message' => $message
                ], $message);
            }

            return ApiResponseResource::success([
                'statistics' => $stats,
                'featured_articles' => $featuredArticles,
                'latest_articles' => $latestArticles['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get dashboard data', [
                'error' => $e->getMessage()
            ]);

            return ApiResponseResource::error('Failed to get dashboard data', null, 500);
        }
    }
}
