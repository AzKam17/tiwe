<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\OrderItemEnum;
use App\Enum\OrderStatus;
use Twig\Attribute\AsTwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class StatusToIcon
{
    #[AsTwigFilter('status_icon')]
    public function formatLib(string $status): string
    {
        return match (strtoupper($status)) {
           'PENDING' => 'bi-gear',
            'CONFIRMED' => 'bi-gear',
            'SHIPPED' => 'bi-truck',
            'DELIVERED' => 'bi-check-circle',
            'CANCELED' => 'bi-x-circle',
        };
    }
}
