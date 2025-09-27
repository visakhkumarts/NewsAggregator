<?php

namespace App\Services;

use App\Models\NewsSource;
use Illuminate\Support\Facades\Log;

class NewsApiService extends BaseNewsService
{
    protected string $serviceName = 'NewsAPI';

    public function __construct(NewsSource $newsSource)
    {
        parent::__construct($newsSource);
    }

    protected function getApiKey(): string
    {
        return config('services.newsapi.key', env('NEWS_API_KEY', ''));
    }

    protected function getBaseUrl(): string
    {
        return 'https://newsapi.org/v2/';
    }

    public function fetchArticles(array $options = []): array
    {
        // NewsAPI requires at least one of: q, qInTitle, sources, or domains
        // We'll use a general search query if none provided
        $searchQuery = $options['q'] ?? 'news';
        
        $params = [
            'apiKey' => $this->apiKey,
            'q' => $searchQuery,
            'pageSize' => $options['pageSize'] ?? 100,
            'sortBy' => $options['sortBy'] ?? 'publishedAt',
            'language' => $options['language'] ?? 'en',
        ];

        // Add category if specified
        if (isset($options['category'])) {
            $params['category'] = $options['category'];
        }

        // Add country if specified
        if (isset($options['country'])) {
            $params['country'] = $options['country'];
        }

        // Add sources if specified
        if (isset($options['sources'])) {
            $params['sources'] = is_array($options['sources']) 
                ? implode(',', $options['sources']) 
                : $options['sources'];
        }

        // Add domains if specified
        if (isset($options['domains'])) {
            $params['domains'] = is_array($options['domains']) 
                ? implode(',', $options['domains']) 
                : $options['domains'];
        }

        $response = $this->makeRequest('everything', $params);

        if (empty($response) || !isset($response['articles'])) {
            return [];
        }

        $articles = [];
        foreach ($response['articles'] as $article) {
            $articles[] = $this->transformArticle($article);
        }

        return $articles;
    }

    protected function transformArticle(array $article): array
    {
        return [
            'external_id' => $article['url'] ?? null,
            'title' => $article['title'] ?? '',
            'description' => $article['description'] ?? null,
            'content' => $article['content'] ?? null,
            'url' => $article['url'] ?? '',
            'image_url' => $article['urlToImage'] ?? null,
            'author' => $article['author'] ?? null,
            'published_at' => $article['publishedAt'] ?? null,
            'category' => $this->extractCategory($article),
            'metadata' => [
                'source_name' => $article['source']['name'] ?? null,
                'source_id' => $article['source']['id'] ?? null,
            ],
        ];
    }

    protected function extractCategory(array $article): ?string
    {
        // NewsAPI doesn't provide direct categories in the everything endpoint
        // We can try to extract from the source or use a default
        $sourceName = $article['source']['name'] ?? '';
        
        // Map common sources to categories
        $categoryMap = [
            'BBC News' => 'General',
            'CNN' => 'General',
            'Reuters' => 'General',
            'Associated Press' => 'General',
            'The Guardian' => 'General',
            'The New York Times' => 'General',
            'TechCrunch' => 'Technology',
            'Wired' => 'Technology',
            'Ars Technica' => 'Technology',
            'ESPN' => 'Sports',
            'BBC Sport' => 'Sports',
            'Bloomberg' => 'Business',
            'Financial Times' => 'Business',
            'Forbes' => 'Business',
        ];

        return $categoryMap[$sourceName] ?? 'General';
    }

    /**
     * Prepare NewsAPI-specific options.
     */
    public function prepareOptions(array $options): array
    {
        // NewsAPI needs a search query, so provide some popular topics
        $topics = ['technology', 'business', 'sports', 'health', 'science', 'politics'];
        $options['q'] = $options['q'] ?? $topics[array_rand($topics)];
        $options['pageSize'] = $options['limit'] ?? 50;
        
        return $options;
    }
}
