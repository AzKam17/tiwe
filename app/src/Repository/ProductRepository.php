<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getTrending()
    {
        return $this->findAll();
    }

    /**
     * Get recently added products as suggestions for inventory entry
     *
     * @param int $limit Maximum number of products to return
     * @return Product[] Returns an array of recently added Product objects
     */
    public function getRecentProductsForSuggestions(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all products for a given order and seller.
     *
     * @param Order $order The order to fetch products from.
     * @param User $buyer The buyer who owns the order.
     * @return Product[] Returns an array of Product objects.
     */
    public function getOrderProducts(Order $order, User $seller): array
    {
        $orderItems = $this->getEntityManager()->createQueryBuilder()
            ->select('oi')
            ->from(OrderItem::class, 'oi')
            ->join('oi.master', 'o')
            ->join('oi.product', 'p')
            ->where('o = :order')
            ->andWhere('p.createdBy = :seller')
            ->setParameter('order', $order)
            ->setParameter('seller', $seller)
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn($oi) => $oi->getProduct(), $orderItems);
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
