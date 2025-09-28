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
use Illuminate\Support\Facades\Log;

class UserPreferenceController extends Controller
{
    /**
     * Get the authenticated user.
     */
    protected function getAuthenticatedUser(): \App\Models\User
    {
        return Auth::user();
    }

    /**
     * Get or create user preferences.
     */
    protected function getOrCreatePreferences(\App\Models\User $user): \App\Models\UserPreference
    {
        $preferences = $user->preferences;
        
        if (!$preferences) {
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
        
        return $preferences;
    }

    /**
     * Get user preferences.
     */
    public function index(): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            $preferences = $this->getOrCreatePreferences($user);

            return ApiResponseResource::success(new UserPreferenceResource($preferences));
        } catch (\Exception $e) {
            Log::error('Failed to get user preferences', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return ApiResponseResource::error('Failed to retrieve user preferences', null, 500);
        }
    }


    /**
     * Update user preferences.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();

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

            $preferences = $this->getOrCreatePreferences($user);

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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseResource::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            Log::error('Failed to update user preferences', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return ApiResponseResource::error('Failed to update preferences', null, 500);
        }
    }

    /**
     * Add preferred source.
     */
    public function addPreferredSource(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();

            $request->validate([
                'source_id' => 'required|integer|exists:news_sources,id'
            ]);

            $preferences = $this->getOrCreatePreferences($user);
            $preferences->addPreferredSource($request->source_id);

            return ApiResponseResource::success(new UserPreferenceResource($preferences), 'Source added to preferences');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseResource::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            Log::error('Failed to add preferred source', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'source_id' => $request->get('source_id')
            ]);
            
            return ApiResponseResource::error('Failed to add source to preferences', null, 500);
        }
    }

    /**
     * Remove preferred source.
     */
    public function removePreferredSource(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();

            $request->validate([
                'source_id' => 'required|integer|exists:news_sources,id'
            ]);

            $preferences = $user->preferences;
            
            if ($preferences) {
                $preferences->removePreferredSource($request->source_id);
            }

            return ApiResponseResource::success(new UserPreferenceResource($preferences), 'Source removed from preferences');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseResource::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            Log::error('Failed to remove preferred source', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'source_id' => $request->get('source_id')
            ]);
            
            return ApiResponseResource::error('Failed to remove source from preferences', null, 500);
        }
    }

    /**
     * Add preferred category.
     */
    public function addPreferredCategory(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();

            $request->validate([
                'category_id' => 'required|integer|exists:categories,id'
            ]);

            $preferences = $this->getOrCreatePreferences($user);
            $preferences->addPreferredCategory($request->category_id);

            return ApiResponseResource::success(new UserPreferenceResource($preferences), 'Category added to preferences');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseResource::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            Log::error('Failed to add preferred category', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
                'category_id' => $request->get('category_id')
            ]);
            
            return ApiResponseResource::error('Failed to add category to preferences', null, 500);
        }
    }

    /**
     * Remove preferred category.
     */
    public function removePreferredCategory(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();

            $request->validate([
                'category_id' => 'required|integer|exists:categories,id'
            ]);

            $preferences = $user->preferences;
            
            if ($preferences) {
                $preferences->removePreferredCategory($request->category_id);
            }

            return ApiResponseResource::success(new UserPreferenceResource($preferences), 'Category removed from preferences');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseResource::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            Log::error('Failed to remove preferred category', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
                'category_id' => $request->get('category_id')
            ]);
            
            return ApiResponseResource::error('Failed to remove category from preferences', null, 500);
        }
    }

    /**
     * Get personalized articles based on user preferences.
     */
    public function personalizedArticles(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();

            $preferences = $user->preferences;
        
            if (!$preferences) {
                // Return regular articles if no preferences
                $filters = $request->only(['search', 'date_from', 'date_to', 'featured']);
                
                // Convert featured string to boolean
                if (isset($filters['featured'])) {
                    $filters['featured'] = filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                }
                
                $service = app(\App\Services\NewsAggregatorService::class);
                $result = $service->getArticles($filters, $request->get('page', 1), $request->get('per_page', 20));
                
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

            // Convert featured string to boolean
            if (isset($filters['featured'])) {
                $filters['featured'] = filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

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

            // Check if no results found
            if (empty($result['data']) && $result['pagination']['total'] === 0) {
                $message = $this->buildPersonalizedNoResultsMessage($filters, $preferences);
                
                return ApiResponseResource::success([
                    'data' => [],
                    'pagination' => $result['pagination'],
                    'preferences_applied' => [
                        'preferred_sources' => $preferences->preferred_sources ?? [],
                        'preferred_categories' => $preferences->preferred_categories ?? [],
                        'preferred_authors' => $preferences->preferred_authors ?? []
                    ],
                    'message' => $message
                ], $message);
            }

            return ApiResponseResource::success([
                'data' => \App\Http\Resources\ArticleResource::collection($result['data']),
                'pagination' => $result['pagination'],
                'preferences_applied' => [
                    'sources' => $preferences->preferred_sources ?? [],
                    'categories' => $preferences->preferred_categories ?? [],
                    'authors' => $preferences->preferred_authors ?? [],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get personalized articles', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return ApiResponseResource::error('Failed to retrieve personalized articles', null, 500);
        }
    }

    /**
     * Build a descriptive message when no personalized results are found.
     */
    protected function buildPersonalizedNoResultsMessage(array $filters, \App\Models\UserPreference $preferences): string
    {
        $conditions = [];
        
        if (isset($filters['search'])) {
            $conditions[] = "search term '{$filters['search']}'";
        }
        
        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $conditions[] = "date range from {$filters['date_from']} to {$filters['date_to']}";
        }
        
        if (isset($filters['featured'])) {
            $featuredText = $filters['featured'] ? 'featured' : 'non-featured';
            $conditions[] = "{$featuredText} articles";
        }
        
        $preferenceConditions = [];
        
        if (!empty($preferences->preferred_sources)) {
            $sourceNames = \App\Models\NewsSource::whereIn('id', $preferences->preferred_sources)->pluck('name')->toArray();
            $preferenceConditions[] = "preferred sources: " . implode(', ', $sourceNames);
        }
        
        if (!empty($preferences->preferred_categories)) {
            $categoryNames = \App\Models\Category::whereIn('id', $preferences->preferred_categories)->pluck('name')->toArray();
            $preferenceConditions[] = "preferred categories: " . implode(', ', $categoryNames);
        }
        
        if (!empty($preferences->preferred_authors)) {
            $preferenceConditions[] = "preferred authors: " . implode(', ', $preferences->preferred_authors);
        }
        
        $message = "No personalized articles found";
        
        if (!empty($conditions)) {
            $message .= " matching " . implode(', ', $conditions);
        }
        
        if (!empty($preferenceConditions)) {
            $message .= " with your preferences (" . implode(', ', $preferenceConditions) . ")";
        }
        
        $message .= ".";
        
        return $message;
    }
}