<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\NewsContentBlock;
use App\Enums\BlockType;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsContentBlockFactory extends Factory
{
    protected $model = NewsContentBlock::class;

    public function definition(): array
    {
        $type = fake()->randomElement(BlockType::cases());

        return [
            'news_id' => News::factory(),
            'type' => $type->value,

            'text_content' => in_array($type, [BlockType::Text, BlockType::TextImageRight, BlockType::TextImageLeft])
                ? fake()->realText(300)
                : null,

            'image_path' => in_array($type, [BlockType::Image, BlockType::TextImageRight, BlockType::TextImageLeft])
                ? 'https://picsum.photos/seed/' . fake()->uuid() . '/640/480'
                : null,

            'order' => 0,
        ];
    }
}
