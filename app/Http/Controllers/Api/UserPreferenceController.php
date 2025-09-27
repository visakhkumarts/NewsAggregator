<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseResource;
use App\Http\Resources\UserPreferenceResource;
use App\Models\UserPreference;
use App\Models\NewsSource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    /**
     * Get user preferences.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return ApiResponseResource::error('User not authenticated', null, 401);
        }

        $preferences = $user->preferences;
        
        if (!$preferences) {
            // Create default preferences if none exist
            $preferences = UserPreference::create([
                'user_id' => $user->id,
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
                'language' => 'en',
                'country' => 'us',
                'articles_per_page' => 20,
                'show_images' => true,
                'auto_refresh' => false,
                'refresh_interval' => 300,
            ]);
        }

        return ApiResponseResource::success(new UserPreferenceResource($preferences));
    }

    /**
     * Update user preferences.
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return ApiResponseResource::error('User not authenticated', null, 401);
        }

        $request->validate([
            'preferred_sources' => 'nullable|array',
            'preferred_sources.*' => 'integer|exists:news_sources,id',
            'preferred_categories' => 'nullable|array',
            'preferred_categories.*' => 'integer|exists:categories,id',
            'preferred_authors' => 'nullable|array',
            'preferred_authors.*' => 'string|max:255',
            'language' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:10',
            'articles_per_page' => 'nullable|integer|min:1|max:100',
            'show_images' => 'nullable|boolean',
            'auto_refresh' => 'nullable|boolean',
            'refresh_interval' => 'nullable|integer|min:60|max:3600',
        ]);

        $preferences = $user->preferences;
        
        if (!$preferences) {
            $preferences = new UserPreference();
            $preferences->user_id = $user->id;
        }

        $preferences->fill($request->only([
            'preferred_sources',
            'preferred_categories', 
            'preferred_authors',
            'language',
            'country',
            'articles_per_page',
            'show_images',
            'auto_refresh',
            'refresh_interval',
        ]));

        $preferences->save();

        return ApiResponseResource::success(new UserPreferenceResource($preferences), 'Preferences updated successfully');
    }

    /**
     * Add preferred source.
     */
    public function addPreferredSource(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return ApiResponseResource::error('User not authenticated', null, 401);
        }

        $request->validate([
            'source_id' => 'required|integer|exists:news_sources,id'
        ]);

        $preferences = $user->preferences;
        
        if (!$preferences) {
            $preferences = UserPreference::create([
                'user_id' => $user->id,
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
            ]);
        }

        $preferences->addPreferredSource($request->source_id);

        return ApiResponseResource::success(new UserPreferenceResource($preferences), 'Source added to preferences');
    }

    /**
     * Remove preferred source.
     */
    public function removePreferredSource(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return ApiResponseResource::error('User not authenticated', null, 401);
        }

        $request->validate([
            'source_id' => 'required|integer|exists:news_sources,id'
        ]);

        $preferences = $user->preferences;
        
        if ($preferences) {
            $preferences->removePreferredSource($request->source_id);
        }

        return ApiResponseResource::success(new UserPreferenceResource($preferences), 'Source removed from preferences');
    }

    /**
     * Add preferred category.
     */
    public function addPreferredCategory(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return ApiResponseResource::error('User not authenticated', null, 401);
        }

        $request->validate([
            'category_id' => 'required|integer|exists:categories,id'
        ]);

        $preferences = $user->preferences;
        
        if (!$preferences) {
            $preferences = UserPreference::create([
                'user_id' => $user->id,
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
            ]);
        }

        $preferences->addPreferredCategory($request->category_id);

        return ApiResponseResource::success(new UserPreferenceResource($preferences), 'Category added to preferences');
    }

    /**
     * Remove preferred category.
     */
    public function removePreferredCategory(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return ApiResponseResource::error('User not authenticated', null, 401);
        }

        $request->validate([
            'category_id' => 'required|integer|exists:categories,id'
        ]);

        $preferences = $user->preferences;
        
        if ($preferences) {
            $preferences->removePreferredCategory($request->category_id);
        }

        return ApiResponseResource::success(new UserPreferenceResource($preferences), 'Category removed from preferences');
    }

    /**
     * Get personalized articles based on user preferences.
     */
    public function personalizedArticles(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return ApiResponseResource::error('User not authenticated', null, 401);
        }

        $preferences = $user->preferences;
        
        if (!$preferences) {
            // Return regular articles if no preferences
            $service = app(\App\Services\NewsAggregatorService::class);
            $result = $service->getArticles($request->only(['search', 'date_from', 'date_to', 'featured']), $request->get('page', 1), $request->get('per_page', 20));
            
            return ApiResponseResource::success([
                'data' => \App\Http\Resources\ArticleResource::collection($result['data']),
                'pagination' => $result['pagination'],
                'preferences_applied' => []
            ]);
        }

        // Build filters based on user preferences
        $filters = $request->only([
            'search', 'date_from', 'date_to', 'featured'
        ]);

        // Add user preferences to filters
        if ($preferences->preferred_sources) {
            $filters['preferred_sources'] = $preferences->preferred_sources;
        }
        
        if ($preferences->preferred_categories) {
            $filters['preferred_categories'] = $preferences->preferred_categories;
        }
        
        if ($preferences->preferred_authors) {
            $filters['preferred_authors'] = $preferences->preferred_authors;
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', $preferences->articles_per_page ?? 20);

        // Use the news aggregator service with personalized filters
        $service = app(\App\Services\NewsAggregatorService::class);
        $result = $service->getPersonalizedArticles($filters, $page, $perPage);

        return ApiResponseResource::success([
            'data' => \App\Http\Resources\ArticleResource::collection($result['data']),
            'pagination' => $result['pagination'],
            'preferences_applied' => [
                'sources' => $preferences->preferred_sources ?? [],
                'categories' => $preferences->preferred_categories ?? [],
                'authors' => $preferences->preferred_authors ?? [],
            ]
        ]);
    }
}