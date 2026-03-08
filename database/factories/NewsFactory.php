<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        if (! Storage::disk('public')->exists('news')) {
            Storage::disk('public')->makeDirectory('news');
        }

        $filename = fake()->uuid().'.jpg';

        $imageContent = file_get_contents('https://picsum.photos/800/600');
        Storage::disk('public')->put('news/'.$filename, $imageContent);

        return [
            'user_id' => User::factory(),
            'title' => fake()->realText(50),
            'image' => 'news/'.$filename,
            'short_description' => fake()->realText(150),
            'is_published' => fake()->boolean(70),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
