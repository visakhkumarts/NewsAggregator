<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseResource;
use App\Http\Resources\NewsSourceResource;
use App\Models\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NewsSourceController extends Controller
{
    /**
     * Display a listing of news sources.
     */
    public function index(Request $request): JsonResponse
    {
        $query = NewsSource::query();

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Order by priority and name
        $sources = $query->ordered()->get();

        return ApiResponseResource::success(NewsSourceResource::collection($sources));
    }

    /**
     * Display the specified news source.
     */
    public function show(string $id): JsonResponse
    {
        $source = NewsSource::withCount('articles')->find($id);

        if (!$source) {
            return ApiResponseResource::notFound('News source not found');
        }

        return ApiResponseResource::success(new NewsSourceResource($source));
    }

    /**
     * Get active news sources.
     */
    public function active(): JsonResponse
    {
        $sources = NewsSource::active()->ordered()->get();

        return ApiResponseResource::success(NewsSourceResource::collection($sources));
    }

    /**
     * Get news source statistics.
     */
    public function statistics(): JsonResponse
    {
        $sources = NewsSource::withCount('articles')
            ->orderBy('articles_count', 'desc')
            ->get();

        return ApiResponseResource::success(NewsSourceResource::collection($sources));
    }
}
