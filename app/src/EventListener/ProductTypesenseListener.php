<?php

namespace App\EventListener;

use App\Entity\Product;
use App\Service\ProductIndexerService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

#[AsEntityListener(event: Events::postPersist, entity: Product::class)]
#[AsEntityListener(event: Events::postUpdate, entity: Product::class)]
#[AsEntityListener(event: Events::postRemove, entity: Product::class)]
class ProductTypesenseListener
{
    public function __construct(
        private readonly ProductIndexerService $productIndexer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function postPersist(Product $product, PostPersistEventArgs $args): void
    {
        try {
            $this->productIndexer->indexProduct($product);
        } catch (\Exception $e) {
            $this->logger->error('Failed to index product after persist', [
                'product_id' => $product->getId(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function postUpdate(Product $product, PostUpdateEventArgs $args): void
    {
        try {
            $this->productIndexer->indexProduct($product);
        } catch (\Exception $e) {
            $this->logger->error('Failed to index product after update', [
                'product_id' => $product->getId(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function postRemove(Product $product, PostRemoveEventArgs $args): void
    {
        try {
            $this->productIndexer->deleteProduct($product);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete product from index after removal', [
                'product_id' => $product->getId(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
