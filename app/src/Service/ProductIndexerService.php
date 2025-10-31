<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Psr\Log\LoggerInterface;

class ProductIndexerService
{
    private const COLLECTION_NAME = 'products';

    public function __construct(
        private readonly TypesenseClientService $typesenseClient,
        private readonly ProductRepository $productRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createCollection(): void
    {
        $schema = [
            'name' => self::COLLECTION_NAME,
            'fields' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                ],
                [
                    'name' => 'sortable_id',
                    'type' => 'int32',
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                ],
                [
                    'name' => 'description',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'category_id',
                    'type' => 'int32',
                    'optional' => true,
                    'facet' => true,
                ],
                [
                    'name' => 'category_name',
                    'type' => 'string',
                    'optional' => true,
                    'facet' => true,
                ],
                [
                    'name' => 'measurement_type',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'measurement_unit',
                    'type' => 'string',
                    'optional' => true,
                ],
                [
                    'name' => 'images',
                    'type' => 'string[]',
                    'optional' => true,
                ],
                [
                    'name' => 'created_by_id',
                    'type' => 'string',
                ],
            ],
            'default_sorting_field' => 'sortable_id',
        ];

        try {
            $this->typesenseClient->createCollection($schema);
            $this->logger->info('Typesense collection created', ['collection' => self::COLLECTION_NAME]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Typesense collection', [
                'collection' => self::COLLECTION_NAME,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function deleteCollection(): void
    {
        try {
            $this->typesenseClient->deleteCollection(self::COLLECTION_NAME);
            $this->logger->info('Typesense collection deleted', ['collection' => self::COLLECTION_NAME]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete Typesense collection', [
                'collection' => self::COLLECTION_NAME,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function indexProduct(Product $product): void
    {
        $document = $this->transformProductToDocument($product);

        try {
            $this->typesenseClient
                ->getCollection(self::COLLECTION_NAME)
                ->documents
                ->upsert($document);

            $this->logger->info('Product indexed', ['product_id' => $product->getId()]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to index product', [
                'product_id' => $product->getId(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function deleteProduct(Product $product): void
    {
        try {
            $this->typesenseClient
                ->getCollection(self::COLLECTION_NAME)
                ->documents[(string) $product->getId()]
                ->delete();

            $this->logger->info('Product deleted from index', ['product_id' => $product->getId()]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete product from index', [
                'product_id' => $product->getId(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function indexAllProducts(): int
    {
        $products = $this->productRepository->findAll();
        $count = 0;

        foreach ($products as $product) {
            try {
                $this->indexProduct($product);
                ++$count;
            } catch (\Exception $e) {
                $this->logger->error('Failed to index product', [
                    'product_id' => $product->getId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->logger->info('Indexed products', ['count' => $count]);

        return $count;
    }

    private function transformProductToDocument(Product $product): array
    {
        return [
            'id' => (string) $product->getId(),
            'sortable_id' => $product->getId(),
            'title' => $product->getTitle(),
            'description' => $product->getDescription() ?? '',
            'category_id' => $product->getCategory()?->getId() ?? 0,
            'category_name' => $product->getCategory()?->getTitle() ?? '',
            'measurement_type' => $product->getMeasurementType() ?? '',
            'measurement_unit' => $product->getMeasurementUnit() ?? '',
            'images' => $product->getImages() ?? [],
            'created_by_id' => (string) $product->getCreatedBy()->getId(),
        ];
    }
}
