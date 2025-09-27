<?php

namespace App\Services;

use App\Models\NewsSource;
use Illuminate\Support\Facades\Log;

class NewYorkTimesApiService extends BaseNewsService
{
    protected string $serviceName = 'New York Times';

    public function __construct(NewsSource $newsSource)
    {
        parent::__construct($newsSource);
    }

    protected function getApiKey(): string
    {
        return config('services.nytimes.key', env('NYTIMES_API_KEY', ''));
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.nytimes.com/svc/';
    }

    public function fetchArticles(array $options = []): array
    {
        $params = [
            'api-key' => $this->apiKey,
        ];

        // Add search query if specified
        if (isset($options['q'])) {
            $params['q'] = $options['q'];
        }

        // Add begin date if specified
        if (isset($options['beginDate'])) {
            $params['begin_date'] = $options['beginDate'];
        }

        // Add end date if specified
        if (isset($options['endDate'])) {
            $params['end_date'] = $options['endDate'];
        }

        // Add sort if specified
        if (isset($options['sort'])) {
            $params['sort'] = $options['sort'];
        }

        // Add page if specified
        if (isset($options['page'])) {
            $params['page'] = $options['page'];
        }

        $response = $this->makeRequest('search/v2/articlesearch.json', $params);

        if (empty($response) || !isset($response['response']['docs'])) {
            return [];
        }

        $articles = [];
        foreach ($response['response']['docs'] as $article) {
            $articles[] = $this->transformArticle($article);
        }

        return $articles;
    }

    protected function transformArticle(array $article): array
    {
        $headline = $article['headline'] ?? [];
        $multimedia = $article['multimedia'] ?? [];
        $byline = $article['byline'] ?? [];
        $keywords = $article['keywords'] ?? [];

        return [
            'external_id' => $article['_id'] ?? null,
            'title' => $headline['main'] ?? $headline['print_headline'] ?? '',
            'description' => $article['abstract'] ?? null,
            'content' => $article['lead_paragraph'] ?? null,
            'url' => $article['web_url'] ?? '',
            'image_url' => $this->getImageUrl($multimedia),
            'author' => $byline['original'] ?? null,
            'published_at' => $article['pub_date'] ?? null,
            'category' => $this->extractCategory($article, $keywords),
            'metadata' => [
                'section' => $article['section_name'] ?? null,
                'subsection' => $article['subsection_name'] ?? null,
                'document_type' => $article['document_type'] ?? null,
                'type_of_material' => $article['type_of_material'] ?? null,
                'word_count' => $article['word_count'] ?? null,
                'keywords' => $keywords,
                'multimedia' => $multimedia,
            ],
        ];
    }

    protected function getImageUrl(array $multimedia): ?string
    {
        if (empty($multimedia)) {
            return null;
        }

        // Find the largest image
        $largestImage = null;
        $maxWidth = 0;

        foreach ($multimedia as $media) {
            if (isset($media['width']) && $media['width'] > $maxWidth) {
                $maxWidth = $media['width'];
                $largestImage = $media;
            }
        }

        if ($largestImage && isset($largestImage['url'])) {
            return 'https://www.nytimes.com/' . $largestImage['url'];
        }

        return null;
    }

    protected function extractCategory(array $article, array $keywords): ?string
    {
        // Try to get category from section name first
        $sectionName = $article['section_name'] ?? '';
        if ($sectionName) {
            return $this->mapSectionToCategory($sectionName);
        }

        // Try to extract from keywords
        foreach ($keywords as $keyword) {
            if (isset($keyword['name']) && $keyword['name'] === 'subject') {
                $value = $keyword['value'] ?? '';
                if ($this->isCategoryKeyword($value)) {
                    return $value;
                }
            }
        }

        return 'General';
    }

    protected function mapSectionToCategory(string $section): string
    {
        $categoryMap = [
            'Sports' => 'Sports',
            'Technology' => 'Technology',
            'Business' => 'Business',
            'Politics' => 'Politics',
            'World' => 'World',
            'Science' => 'Science',
            'Health' => 'Health',
            'Arts' => 'Culture',
            'Style' => 'Lifestyle',
            'Food' => 'Food',
            'Travel' => 'Travel',
            'Real Estate' => 'Real Estate',
            'Education' => 'Education',
            'Opinion' => 'Opinion',
            'U.S.' => 'US News',
            'New York' => 'Local News',
        ];

        return $categoryMap[$section] ?? 'General';
    }

    protected function isCategoryKeyword(string $keyword): bool
    {
        $categoryKeywords = [
            'Technology', 'Sports', 'Business', 'Politics', 'World',
            'Science', 'Health', 'Culture', 'Lifestyle', 'Food',
            'Travel', 'Education', 'Opinion', 'US News', 'Local News'
        ];

        return in_array($keyword, $categoryKeywords);
    }

    /**
     * Prepare New York Times API-specific options.
     */
    public function prepareOptions(array $options): array
    {
        $options['page'] = 0; // NYT uses 0-based pagination
        
        return $options;
    }
}

