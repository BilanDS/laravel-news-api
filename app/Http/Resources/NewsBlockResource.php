<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class NewsBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'text_content' => $this->text_content,
            'image' => $this->image_path ? Storage::url($this->image_path) : null,
            'order' => $this->order,
        ];
    }
}
