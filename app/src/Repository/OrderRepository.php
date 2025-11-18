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
     * Get all orders that include at least one inventory entry from a specific seller.
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
            ->innerJoin('oi.inventoryEntry', 'ie')
            ->andWhere('ie.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        $orders = $qb->getQuery()->getResult();

        foreach ($orders as $order) {
            // Filter items to only show those from the current seller's inventory
            $itemsToKeep = array_filter(
                $order->getItems()->toArray(),
                function($item) use ($user) {
                    $inventoryEntry = $item->getInventoryEntry();
                    return $inventoryEntry && $inventoryEntry->getUser()->getId() === $user->getId();
                }
            );

            $order->emptyItems()->addItems($itemsToKeep);
            $order->computeAmount()->computeTotalAmount();
        }

        return $orders;
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
