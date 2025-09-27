<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General news and current events',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Technology news, gadgets, and innovation',
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Business news, finance, and economy',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'description' => 'Sports news, scores, and updates',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'Health',
                'slug' => 'health',
                'description' => 'Health news, medical research, and wellness',
                'color' => '#8B5CF6',
                'is_active' => true,
            ],
            [
                'name' => 'Science',
                'slug' => 'science',
                'description' => 'Scientific discoveries and research',
                'color' => '#06B6D4',
                'is_active' => true,
            ],
            [
                'name' => 'Politics',
                'slug' => 'politics',
                'description' => 'Political news and government updates',
                'color' => '#84CC16',
                'is_active' => true,
            ],
            [
                'name' => 'World',
                'slug' => 'world',
                'description' => 'International news and global events',
                'color' => '#F97316',
                'is_active' => true,
            ],
            [
                'name' => 'Culture',
                'slug' => 'culture',
                'description' => 'Arts, entertainment, and cultural news',
                'color' => '#EC4899',
                'is_active' => true,
            ],
            [
                'name' => 'Lifestyle',
                'slug' => 'lifestyle',
                'description' => 'Lifestyle, fashion, and personal interest stories',
                'color' => '#14B8A6',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
