<?php

namespace App\Services;

use App\Models\NewsSource;
use Illuminate\Support\Facades\Log;

class GuardianApiService extends BaseNewsService
{
    protected string $serviceName = 'The Guardian';

    public function __construct(NewsSource $newsSource)
    {
        parent::__construct($newsSource);
    }

    protected function getApiKey(): string
    {
        return config('services.guardian.key', env('GUARDIAN_API_KEY', ''));
    }

    protected function getBaseUrl(): string
    {
        return 'https://content.guardianapis.com/';
    }

    public function fetchArticles(array $options = []): array
    {
        $params = [
            'api-key' => $this->apiKey,
            'page-size' => $options['pageSize'] ?? 50,
            'order-by' => $options['orderBy'] ?? 'newest',
            'show-fields' => 'headline,trailText,body,thumbnail,byline,publication',
            'show-tags' => 'all',
        ];

        // Add section if specified
        if (isset($options['section'])) {
            $params['section'] = $options['section'];
        }

        // Add search query if specified
        if (isset($options['q'])) {
            $params['q'] = $options['q'];
        }

        // Add date range if specified
        if (isset($options['fromDate'])) {
            $params['from-date'] = $options['fromDate'];
        }

        if (isset($options['toDate'])) {
            $params['to-date'] = $options['toDate'];
        }

        $response = $this->makeRequest('search', $params);

        if (empty($response) || !isset($response['response']['results'])) {
            return [];
        }

        $articles = [];
        foreach ($response['response']['results'] as $article) {
            $articles[] = $this->transformArticle($article);
        }

        return $articles;
    }

    protected function transformArticle(array $article): array
    {
        $fields = $article['fields'] ?? [];
        $tags = $article['tags'] ?? [];

        return [
            'external_id' => $article['id'] ?? null,
            'title' => $fields['headline'] ?? $article['webTitle'] ?? '',
            'description' => $fields['trailText'] ?? null,
            'content' => $fields['body'] ?? null,
            'url' => $article['webUrl'] ?? '',
            'image_url' => $fields['thumbnail'] ?? null,
            'author' => $fields['byline'] ?? $this->extractAuthorFromTags($tags),
            'published_at' => $article['webPublicationDate'] ?? null,
            'category' => $this->extractCategory($article, $tags),
            'metadata' => [
                'section_id' => $article['sectionId'] ?? null,
                'section_name' => $article['sectionName'] ?? null,
                'pillar_id' => $article['pillarId'] ?? null,
                'pillar_name' => $article['pillarName'] ?? null,
                'tags' => $tags,
            ],
        ];
    }

    protected function extractCategory(array $article, array $tags): ?string
    {
        // Try to get category from section name first
        $sectionName = $article['sectionName'] ?? '';
        if ($sectionName) {
            return $this->mapSectionToCategory($sectionName);
        }

        // Try to get category from pillar name
        $pillarName = $article['pillarName'] ?? '';
        if ($pillarName) {
            return $this->mapPillarToCategory($pillarName);
        }

        // Try to extract from tags
        foreach ($tags as $tag) {
            if (isset($tag['type']) && $tag['type'] === 'keyword') {
                $tagName = $tag['webTitle'] ?? '';
                if ($this->isCategoryTag($tagName)) {
                    return $tagName;
                }
            }
        }

        return 'General';
    }

    protected function mapSectionToCategory(string $section): string
    {
        $categoryMap = [
            'sport' => 'Sports',
            'technology' => 'Technology',
            'business' => 'Business',
            'politics' => 'Politics',
            'world' => 'World',
            'uk-news' => 'UK News',
            'us-news' => 'US News',
            'science' => 'Science',
            'environment' => 'Environment',
            'culture' => 'Culture',
            'lifeandstyle' => 'Lifestyle',
            'fashion' => 'Fashion',
            'food' => 'Food',
            'travel' => 'Travel',
            'money' => 'Finance',
            'media' => 'Media',
            'education' => 'Education',
            'health' => 'Health',
        ];

        return $categoryMap[$section] ?? 'General';
    }

    protected function mapPillarToCategory(string $pillar): string
    {
        $categoryMap = [
            'news' => 'General',
            'opinion' => 'Opinion',
            'sport' => 'Sports',
            'arts' => 'Culture',
            'lifestyle' => 'Lifestyle',
        ];

        return $categoryMap[$pillar] ?? 'General';
    }

    protected function isCategoryTag(string $tagName): bool
    {
        $categoryTags = [
            'Technology', 'Sports', 'Business', 'Politics', 'World',
            'Science', 'Environment', 'Culture', 'Lifestyle', 'Health',
            'Education', 'Finance', 'Media', 'Food', 'Travel', 'Fashion'
        ];

        return in_array($tagName, $categoryTags);
    }

    protected function extractAuthorFromTags(array $tags): ?string
    {
        foreach ($tags as $tag) {
            if (isset($tag['type']) && $tag['type'] === 'contributor') {
                return $tag['webTitle'] ?? null;
            }
        }

        return null;
    }

    /**
     * Prepare Guardian API-specific options.
     */
    public function prepareOptions(array $options): array
    {
        $options['pageSize'] = $options['limit'] ?? 50;
        
        return $options;
    }
}

