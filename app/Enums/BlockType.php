<?php

namespace App\Enums;

enum BlockType: string
{
    case Text = 'text';
    case Image = 'image';
    case TextImageRight = 'text_image_right';
    case TextImageLeft = 'text_image_left';
}
