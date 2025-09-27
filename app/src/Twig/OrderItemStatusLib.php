<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\OrderItemEnum;
use Twig\Attribute\AsTwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class OrderItemStatusLib
{
    #[AsTwigFilter('order_item_status_lib')]
    public function formatLib(OrderItemEnum $enum): string
    {
        return match ($enum) {
            OrderItemEnum::PENDING => 'En attente',
            OrderItemEnum::CONFIRMED => 'En cours de traitement',
            OrderItemEnum::SHIPPED => 'Expédié',
            OrderItemEnum::DELIVERED => 'Livré',
            OrderItemEnum::CANCELED => 'Annulé',
        };
    }
}
