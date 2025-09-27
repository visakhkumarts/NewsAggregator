<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache TTL Configuration
    |--------------------------------------------------------------------------
    |
    | Define cache time-to-live values for different types of data.
    | These values are in seconds.
    |
    */

    'featured_articles' => env('CACHE_TTL_FEATURED_ARTICLES', 300), // 5 minutes
    'category_articles' => env('CACHE_TTL_CATEGORY_ARTICLES', 180), // 3 minutes
    'source_articles' => env('CACHE_TTL_SOURCE_ARTICLES', 180), // 3 minutes
    'statistics' => env('CACHE_TTL_STATISTICS', 300), // 5 minutes
    'news_sources' => env('CACHE_TTL_NEWS_SOURCES', 600), // 10 minutes
    'categories' => env('CACHE_TTL_CATEGORIES', 600), // 10 minutes
    
    /*
    |--------------------------------------------------------------------------
    | Cache Tags
    |--------------------------------------------------------------------------
    |
    | Define cache tags for easy invalidation of related data.
    |
    */
    
    'tags' => [
        'articles' => 'articles',
        'featured' => 'featured',
        'categories' => 'categories',
        'sources' => 'sources',
        'statistics' => 'statistics',
    ],
];
