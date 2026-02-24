<?php

namespace App\Models;

use App\Enums\BlockType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'type',
        'text_content',
        'image_path',
        'order',
    ];

    protected $casts = [
        'type' => BlockType::class,
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }
}
