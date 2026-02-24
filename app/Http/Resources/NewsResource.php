<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'image' => $this->image
                ? (str_starts_with($this->image, 'http') ? $this->image : asset('storage/' . $this->image))
                : null,
            'is_published' => $this->is_published,
            'published_at' => $this->created_at->format('Y-m-d H:i:s'),

            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                ];
            }),

            'blocks' => NewsBlockResource::collection($this->whenLoaded('blocks')),
        ];
    }
}
