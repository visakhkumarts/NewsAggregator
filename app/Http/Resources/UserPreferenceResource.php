<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'preferred_sources' => $this->preferred_sources ?? [],
            'preferred_categories' => $this->preferred_categories ?? [],
            'preferred_authors' => $this->preferred_authors ?? [],
            'language' => $this->language,
            'country' => $this->country,
            'articles_per_page' => $this->articles_per_page,
            'show_images' => $this->show_images,
            'auto_refresh' => $this->auto_refresh,
            'refresh_interval' => $this->refresh_interval,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Include related data
            'preferred_sources_data' => $this->whenLoaded('preferredNewsSources', function () {
                return $this->preferredNewsSources->map(function ($source) {
                    return [
                        'id' => $source->id,
                        'name' => $source->name,
                        'slug' => $source->slug,
                        'logo_url' => $source->logo_url,
                    ];
                });
            }),
            
            'preferred_categories_data' => $this->whenLoaded('preferredCategories', function () {
                return $this->preferredCategories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'color' => $category->color,
                    ];
                });
            }),
        ];
    }
}