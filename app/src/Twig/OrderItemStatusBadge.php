<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\OrderItemEnum;
use Twig\Attribute\AsTwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class OrderItemStatusBadge
{
    #[AsTwigFilter('order_item_status_badge')]
    public function formatLib(OrderItemEnum $enum): string
    {
        return match ($enum) {
            OrderItemEnum::PENDING => 'bg-warning',
            OrderItemEnum::CONFIRMED => 'bg-info',
            OrderItemEnum::SHIPPED => 'bg-primary',
            OrderItemEnum::DELIVERED => 'bg-success',
            OrderItemEnum::CANCELED => 'bg-danger',
        };
    }
}
