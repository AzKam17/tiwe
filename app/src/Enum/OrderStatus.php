<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case DELIVERED = 'DELIVERED';
    case CANCELED = 'CANCELED';
}
