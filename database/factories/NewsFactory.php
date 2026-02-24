<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->realText(50),
            'image' => 'https://picsum.photos/seed/' . fake()->uuid() . '/800/600',
            'short_description' => fake()->realText(150),
            'is_published' => fake()->boolean(70),
        ];
    }

    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_published' => true,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_published' => false,
        ]);
    }
}
