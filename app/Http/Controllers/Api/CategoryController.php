<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $categories = $query->orderBy('name')->get();

        return ApiResponseResource::success(CategoryResource::collection($categories));
    }

    /**
     * Display the specified category.
     */
    public function show(string $id): JsonResponse
    {
        $category = Category::withCount('articles')->find($id);

        if (!$category) {
            return ApiResponseResource::notFound('Category not found');
        }

        return ApiResponseResource::success(new CategoryResource($category));
    }

    /**
     * Get active categories.
     */
    public function active(): JsonResponse
    {
        $categories = Category::active()->orderBy('name')->get();

        return ApiResponseResource::success(CategoryResource::collection($categories));
    }

    /**
     * Get category statistics.
     */
    public function statistics(): JsonResponse
    {
        $categories = Category::withCount('articles')
            ->orderBy('articles_count', 'desc')
            ->get();

        return ApiResponseResource::success(CategoryResource::collection($categories));
    }
}
