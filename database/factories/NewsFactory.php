<?php

namespace Database\Factories;

use App\Models\NewsSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_id' => NewsSource::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'author' => fake()->name(),
            'url' => fake()->url(),
            'image_url' => fake()->imageUrl(640, 480, 'business', true),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'content' => fake()->paragraph(),
        ];
    }
}
