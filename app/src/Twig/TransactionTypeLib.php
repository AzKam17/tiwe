<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\TransactionType;
use Twig\Attribute\AsTwigFilter;
class TransactionTypeLib
{
    #[AsTwigFilter('transaction_type_lib')]
    public function formatLib(TransactionType $enum): string
    {
        return match ($enum) {
            TransactionType::DEPOSIT    => 'Dépôt',
            TransactionType::WITHDRAWAL => 'Retrait',
            TransactionType::TRANSFER   => 'Transfert',
        };
    }
}
