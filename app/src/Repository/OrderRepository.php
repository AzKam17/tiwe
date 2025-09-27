<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Get the purchases made by a specific user.
     *
     * @param User $user The user whose purchases are to be retrieved.
     * @param int $limit The maximum number of purchases to retrieve. Default is 10.
     * @return Order[] An array of Order objects representing the user's purchases.
     */
    public function getMyPurchases($user, $limit = 10): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.buyer = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all orders that include at least one product from a specific seller (store).
     *
     * @param User $user The seller whose orders should be retrieved.
     * @param int|null $limit Optional limit on the number of results.
     * @return Order[] Returns an array of Order objects.
     */
    public function getOrdersForSeller(User $user, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('o')
            ->distinct()
            ->innerJoin('o.items', 'oi')
            ->innerJoin('oi.product', 'p')
            ->innerJoin('p.createdBy', 'seller')
            ->andWhere('seller = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }


    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
