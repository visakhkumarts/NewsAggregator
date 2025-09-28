<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'url' => $this->url,
            'image_url' => $this->image_url,
            'author' => $this->author,
            'published_at' => $this->published_at,
            'formatted_published_at' => $this->formatted_published_at,
            'time_ago' => $this->time_ago,
            'view_count' => $this->view_count,
            'is_featured' => $this->is_featured,
            'metadata' => $this->metadata,
            'news_source' => new NewsSourceResource($this->whenLoaded('newsSource')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}






