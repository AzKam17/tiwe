<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\OrderItemEnum;
use App\Enum\OrderStatus;
use Twig\Attribute\AsTwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class OrderStatusBadge
{
    #[AsTwigFilter('order_status_badge')]
    public function formatLib(OrderStatus $enum): string
    {
        return match ($enum) {
            OrderStatus::PENDING => 'bg-warning',
            OrderStatus::DELIVERED => 'bg-success',
            OrderStatus::CANCELED => 'bg-danger',
        };
    }
}
