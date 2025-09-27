<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'news_source_id' => \App\Models\NewsSource::factory(),
            'category_id' => \App\Models\Category::factory(),
            'external_id' => $this->faker->uuid(),
            'title' => $this->faker->sentence(8),
            'description' => $this->faker->paragraph(3),
            'content' => $this->faker->paragraphs(5, true),
            'url' => $this->faker->url(),
            'image_url' => $this->faker->imageUrl(800, 600),
            'author' => $this->faker->name(),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'metadata' => [
                'source_name' => $this->faker->company(),
                'tags' => $this->faker->words(3),
            ],
            'view_count' => $this->faker->numberBetween(0, 1000),
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
        ];
    }
}
