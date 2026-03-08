<?php

namespace Database\Factories;

use App\Enums\BlockType;
use App\Models\News;
use App\Models\NewsContentBlock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class NewsContentBlockFactory extends Factory
{
    protected $model = NewsContentBlock::class;

    public function definition(): array
    {
        $type = fake()->randomElement(BlockType::cases());
        $imagePath = null;

        if (in_array($type, [BlockType::Image, BlockType::TextImageRight, BlockType::TextImageLeft])) {

            if (!Storage::disk('public')->exists('news_blocks')) {
                Storage::disk('public')->makeDirectory('news_blocks');
            }

            $filename = fake()->uuid() . '.jpg';

            try {
                $imageContent = file_get_contents('https://picsum.photos/600/400');
                Storage::disk('public')->put('news_blocks/' . $filename, $imageContent);
                $imagePath = 'news_blocks/' . $filename;
            } catch (\Exception $e) {
                $imagePath = null;
            }
        }

        return [
            'news_id' => News::factory(),
            'type' => $type->value,

            'text_content' => in_array($type, [BlockType::Text, BlockType::TextImageRight, BlockType::TextImageLeft])
                ? fake()->realText(300)
                : null,

            'image_path' => $imagePath,

            'order' => 0,
        ];
    }
}
