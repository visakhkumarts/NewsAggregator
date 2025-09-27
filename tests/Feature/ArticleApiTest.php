<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Article;
use App\Models\NewsSource;
use App\Models\Category;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    protected NewsSource $newsSource;
    protected Category $category;
    protected Article $article;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->newsSource = NewsSource::factory()->create([
            'name' => 'Test Source',
            'slug' => 'test-source',
            'api_provider' => 'newsapi',
            'is_active' => true,
        ]);

        $this->category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $this->article = Article::factory()->create([
            'news_source_id' => $this->newsSource->id,
            'category_id' => $this->category->id,
            'title' => 'Test Article',
            'url' => 'https://example.com/test-article',
        ]);
    }

    /**
     * Test getting all articles.
     */
    public function test_can_get_articles(): void
    {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'pagination'
                ]);
    }

    /**
     * Test getting a specific article.
     */
    public function test_can_get_specific_article(): void
    {
        $response = $this->getJson("/api/articles/" . $this->article->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'url',
                        'news_source',
                        'category'
                    ]
                ]);
    }

    /**
     * Test getting featured articles.
     */
    public function test_can_get_featured_articles(): void
    {
        $response = $this->getJson('/api/articles/featured');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data'
                ]);
    }

    /**
     * Test getting latest articles.
     */
    public function test_can_get_latest_articles(): void
    {
        $response = $this->getJson('/api/articles/latest');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data'
                ]);
    }

    /**
     * Test searching articles.
     */
    public function test_can_search_articles(): void
    {
        $response = $this->getJson('/api/articles/search?q=test');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'pagination',
                    'query'
                ]);
    }

    /**
     * Test getting articles by category.
     */
    public function test_can_get_articles_by_category(): void
    {
        $response = $this->getJson("/api/articles/category/" . $this->category->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'category'
                ]);
    }

    /**
     * Test getting articles by source.
     */
    public function test_can_get_articles_by_source(): void
    {
        $response = $this->getJson("/api/articles/source/" . $this->newsSource->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'source'
                ]);
    }

    /**
     * Test article not found.
     */
    public function test_article_not_found(): void
    {
        $response = $this->getJson('/api/articles/999999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Article not found'
                ]);
    }
}
