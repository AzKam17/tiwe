<?php

namespace App\Repository;

use App\Entity\InventoryEntry;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InventoryEntry>
 */
class InventoryEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryEntry::class);
    }

    /**
     * Find all inventory entries for a specific user
     *
     * @param User $user
     * @return InventoryEntry[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('ie')
            ->andWhere('ie.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ie.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total inventory value for a user
     *
     * @param User $user
     * @return float
     */
    public function getTotalInventoryValue(User $user): float
    {
        $result = $this->createQueryBuilder('ie')
            ->select('SUM(ie.price * ie.quantity) as total')
            ->andWhere('ie.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }
}
