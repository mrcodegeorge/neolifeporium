<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Initiated = 'initiated';
    case Verified = 'verified';
    case Failed = 'failed';
    case Refunded = 'refunded';
}
