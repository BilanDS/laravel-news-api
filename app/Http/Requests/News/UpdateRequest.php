<?php

namespace App\Http\Requests\News;

use App\Enums\BlockType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'short_description' => ['sometimes', 'required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_published' => ['boolean'],

            'deleted_blocks' => ['nullable', 'array'],
            'deleted_blocks.*' => ['integer', 'exists:news_content_blocks,id'],

            'blocks' => ['nullable', 'array'],

            'blocks.*.id' => ['nullable', 'integer', 'exists:news_content_blocks,id'],
            'blocks.*.type' => ['required', Rule::enum(BlockType::class)],
            'blocks.*.text_content' => ['nullable', 'string'],
            'blocks.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('is_published')) {
            $this->merge([
                'is_published' => filter_var($this->is_published, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
