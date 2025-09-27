<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\TransactionType;
use Twig\Attribute\AsTwigFilter;

class TransactionTypeIcon
{
    #[AsTwigFilter('transaction_type_icon')]
    public function formatIcon(TransactionType $enum): string
    {
        return match ($enum) {
            TransactionType::DEPOSIT    => 'bi bi-arrow-down-circle text-success',
            TransactionType::WITHDRAWAL => 'bi bi-arrow-up-circle text-danger',
            TransactionType::TRANSFER   => 'bi bi-arrow-left-right text-primary',
        };
    }
}
