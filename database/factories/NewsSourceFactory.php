<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsSource>
 */
class NewsSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company() . ' News';
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'api_provider' => $this->faker->randomElement(['newsapi', 'guardian', 'nytimes']),
            'api_endpoint' => $this->faker->randomElement(['everything', 'search', 'search/v2/articlesearch.json']),
            'api_config' => [
                'categories' => ['business', 'technology', 'sports'],
                'languages' => ['en']
            ],
            'is_active' => true,
            'priority' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->sentence(),
            'logo_url' => $this->faker->imageUrl(100, 100),
            'website_url' => $this->faker->url(),
        ];
    }
}
