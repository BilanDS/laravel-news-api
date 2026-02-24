<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'text_content' => $this->text_content,
            'image_path' => $this->image_path
                ? (str_starts_with($this->image_path, 'http') ? $this->image_path : asset('storage/' . $this->image_path))
                : null,
            'order' => $this->order,
        ];
    }
}
