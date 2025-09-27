<?php

namespace App\Services;

use App\Contracts\NewsServiceInterface;
use App\Models\NewsSource;
use Illuminate\Support\Facades\Log;

class NewsServiceFactory
{
    protected array $services = [];

    public function __construct()
    {
        $this->registerServices();
    }

    protected function registerServices(): void
    {
        $this->services = [
            'newsapi' => NewsApiService::class,
            'guardian' => GuardianApiService::class,
            'nytimes' => NewYorkTimesApiService::class,
        ];
    }

    /**
     * Create a news service instance.
     */
    public function create(NewsSource $newsSource): ?NewsServiceInterface
    {
        $provider = $newsSource->api_provider;

        if (!isset($this->services[$provider])) {
            Log::error("Unknown news service provider: {$provider}");
            return null;
        }

        $serviceClass = $this->services[$provider];

        try {
            return new $serviceClass($newsSource);
        } catch (\Exception $e) {
            Log::error("Failed to create news service for provider: {$provider}", [
                'message' => $e->getMessage(),
                'news_source_id' => $newsSource->id
            ]);
            return null;
        }
    }

    /**
     * Get all available service providers.
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->services);
    }

    /**
     * Check if a provider is supported.
     */
    public function isProviderSupported(string $provider): bool
    {
        return isset($this->services[$provider]);
    }
}
