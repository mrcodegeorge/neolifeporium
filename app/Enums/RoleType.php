<?php

namespace App\Enums;

enum RoleType: string
{
    case Farmer = 'farmer';
    case Vendor = 'vendor';
    case Agronomist = 'agronomist';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
