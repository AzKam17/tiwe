<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\OrderItemEnum;
use App\Enum\OrderStatus;
use Twig\Attribute\AsTwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class OrderStatusLib
{
    #[AsTwigFilter('order_status_lib')]
    public function formatLib(OrderStatus $enum): string
    {
        return match ($enum) {
            OrderStatus::PENDING => 'En attente',
            OrderStatus::DELIVERED => 'Livré',
            OrderStatus::CANCELED => 'Annulé',
        };
    }
}
