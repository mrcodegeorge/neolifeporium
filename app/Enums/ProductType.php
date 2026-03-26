<?php

namespace App\Enums;

enum ProductType: string
{
    case Physical = 'physical';
    case Service = 'service';
    case Digital = 'digital';
}
