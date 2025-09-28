<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsSourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'api_provider' => $this->api_provider,
            'api_endpoint' => $this->api_endpoint,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
            'description' => $this->description,
            'logo_url' => $this->logo_url,
            'website_url' => $this->website_url,
            'articles_count' => $this->when(isset($this->articles_count), $this->articles_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}





