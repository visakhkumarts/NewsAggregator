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
            $url = $this->baseUrl . $endpoint;
            $response = Http::timeout(30)->get($url, $params);

            if (!$response->successful()) {
                Log::error("API request failed for {$this->serviceName}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'endpoint' => $endpoint,
                    'url' => $url,
                    'params' => $params
                ]);
                return [];
            }

            $data = $response->json();
            
            if (!$this->validateApiResponse($data)) {
                return [];
            }

            return $data;
        } catch (\Exception $e) {
            Log::error("API request exception for {$this->serviceName}", [
                'message' => $e->getMessage(),
                'endpoint' => $endpoint,
                'url' => $this->baseUrl . $endpoint,
                'params' => $params
            ]);
            return [];
        }
    }

    /**
     * Validate API response data.
     */
    protected function validateApiResponse(array $response): bool
    {
        if (empty($response)) {
            Log::warning("Empty API response from {$this->serviceName}");
            return false;
        }

        return true;
    }

    /**
     * Validate article data before processing.
     */
    protected function validateArticleData(array $data): bool
    {
        $requiredFields = ['title', 'url'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                Log::warning("Missing required field '{$field}' in article data", [
                    'service' => $this->serviceName,
                    'data' => $data
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Parse published date from various formats.
     */
    protected function parsePublishedDate(?string $dateString): Carbon
    {
        if (empty($dateString)) {
            return Carbon::now();
        }

        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$dateString}", [
                'service' => $this->serviceName,
                'error' => $e->getMessage()
            ]);
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
