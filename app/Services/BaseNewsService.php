<?php

namespace App\Services;

use App\Contracts\NewsServiceInterface;
use App\Models\Article;
use App\Models\NewsSource;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

abstract class BaseNewsService implements NewsServiceInterface
{
    protected string $serviceName;
    protected string $apiKey;
    protected string $baseUrl;
    protected NewsSource $newsSource;

    public function __construct(NewsSource $newsSource)
    {
        $this->newsSource = $newsSource;
        $this->apiKey = $this->getApiKey();
        $this->baseUrl = $this->getBaseUrl();
    }

    /**
     * Get the API key for the service.
     */
    abstract protected function getApiKey(): string;

    /**
     * Get the base URL for the service.
     */
    abstract protected function getBaseUrl(): string;

    /**
     * Fetch articles from the news source.
     * Each service must implement this method.
     */
    abstract public function fetchArticles(array $options = []): array;

    /**
     * Make HTTP request to the API.
     */
    protected function makeRequest(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl . $endpoint, $params);

            if (!$response->successful()) {
                Log::error("API request failed for {$this->serviceName}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'endpoint' => $endpoint,
                    'params' => $params
                ]);
                return [];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("API request exception for {$this->serviceName}", [
                'message' => $e->getMessage(),
                'endpoint' => $endpoint,
                'params' => $params
            ]);
            return [];
        }
    }

    /**
     * Store articles in the database.
     */
    protected function storeArticles(array $articles): int
    {
        $storedCount = 0;

        foreach ($articles as $articleData) {
            try {
                $article = $this->createArticle($articleData);
                if ($article) {
                    $storedCount++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to store article for {$this->serviceName}", [
                    'message' => $e->getMessage(),
                    'article_data' => $articleData
                ]);
            }
        }

        return $storedCount;
    }

    /**
     * Create an article from API data.
     */
    protected function createArticle(array $data): ?Article
    {
        // Check if article already exists
        if (isset($data['url']) && Article::where('url', $data['url'])->exists()) {
            return null;
        }

        // Find or create category
        $category = null;
        if (isset($data['category'])) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($data['category'])],
                [
                    'name' => $data['category'],
                    'is_active' => true
                ]
            );
        }

        return Article::create([
            'news_source_id' => $this->newsSource->id,
            'category_id' => $category?->id,
            'external_id' => $data['external_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
            'url' => $data['url'],
            'image_url' => $data['image_url'] ?? null,
            'author' => $data['author'] ?? null,
            'published_at' => $this->parsePublishedDate($data['published_at'] ?? null),
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Parse published date from various formats.
     */
    protected function parsePublishedDate($dateString): Carbon
    {
        if (!$dateString) {
            return Carbon::now();
        }

        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$dateString}");
            return Carbon::now();
        }
    }

    /**
     * Get the service name.
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * Check if the service is available.
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }

    /**
     * Prepare service-specific options.
     * Default implementation returns options as-is.
     * Override in child classes for specific API requirements.
     */
    public function prepareOptions(array $options): array
    {
        return $options;
    }
}
