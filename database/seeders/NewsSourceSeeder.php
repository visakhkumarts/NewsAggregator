<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NewsSource;

class NewsSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'NewsAPI',
                'slug' => 'newsapi',
                'api_provider' => 'newsapi',
                'api_endpoint' => 'everything',
                'api_config' => [
                    'categories' => ['business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology'],
                    'countries' => ['us', 'gb', 'ca', 'au'],
                    'languages' => ['en']
                ],
                'is_active' => true,
                'priority' => 100,
                'description' => 'Comprehensive news API with access to over 70,000 news sources',
                'logo_url' => 'https://newsapi.org/images/logo.png',
                'website_url' => 'https://newsapi.org',
            ],
            [
                'name' => 'The Guardian',
                'slug' => 'guardian',
                'api_provider' => 'guardian',
                'api_endpoint' => 'search',
                'api_config' => [
                    'sections' => ['world', 'uk-news', 'us-news', 'sport', 'technology', 'business', 'science', 'culture'],
                    'order_by' => 'newest'
                ],
                'is_active' => true,
                'priority' => 90,
                'description' => 'The Guardian newspaper API providing high-quality journalism',
                'logo_url' => 'https://assets.guim.co.uk/images/guardian-logo-rss.png',
                'website_url' => 'https://www.theguardian.com',
            ],
            [
                'name' => 'New York Times',
                'slug' => 'nytimes',
                'api_provider' => 'nytimes',
                'api_endpoint' => 'search/v2/articlesearch.json',
                'api_config' => [
                    'sections' => ['World', 'U.S.', 'Politics', 'Business', 'Technology', 'Science', 'Health', 'Sports', 'Arts'],
                    'sort' => 'newest'
                ],
                'is_active' => true,
                'priority' => 80,
                'description' => 'The New York Times API for premium news content',
                'logo_url' => 'https://static01.nyt.com/images/misc/nytlogo379x64.gif',
                'website_url' => 'https://www.nytimes.com',
            ],
        ];

        foreach ($sources as $source) {
            NewsSource::updateOrCreate(
                ['slug' => $source['slug']],
                $source
            );
        }
    }
}
