<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;

class ProductSearchService
{
    private const COLLECTION_NAME = 'products';

    public function __construct(
        private readonly TypesenseClientService $typesenseClient,
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * Search products by query string
     *
     * @param string $query The search query
     * @param array $options Additional search options
     * @return array Raw Typesense search results
     */
    public function search(string $query, array $options = []): array
    {
        $searchParameters = array_merge([
            'q' => $query,
            'query_by' => 'title,description,category_name',
            'per_page' => 20,
            'page' => 1,
        ], $options);

        try {
            $results = $this->typesenseClient
                ->getCollection(self::COLLECTION_NAME)
                ->documents
                ->search($searchParameters);

            return $results;
        } catch (\Exception $e) {
            throw new \RuntimeException('Search failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Search products and return hydrated Product entities
     *
     * @param string $query The search query
     * @param array $options Additional search options
     * @return Product[]
     */
    public function searchProducts(string $query, array $options = []): array
    {
        $results = $this->search($query, $options);

        if (empty($results['hits'])) {
            return [];
        }

        $productIds = array_map(
            fn($hit) => (int) $hit['document']['id'],
            $results['hits']
        );

        // Get products from database maintaining search order
        $products = $this->productRepository->findBy(['id' => $productIds]);

        // Sort by search relevance
        $productsById = [];
        foreach ($products as $product) {
            $productsById[$product->getId()] = $product;
        }

        $orderedProducts = [];
        foreach ($productIds as $id) {
            if (isset($productsById[$id])) {
                $orderedProducts[] = $productsById[$id];
            }
        }

        return $orderedProducts;
    }

    /**
     * Autocomplete search for products
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function autocomplete(string $query, int $limit = 10): array
    {
        return $this->search($query, [
            'query_by' => 'title',
            'per_page' => $limit,
            'prefix' => true,
            'num_typos' => 1,
            'drop_tokens_threshold' => 1,
        ]);
    }

    /**
     * Search by category
     *
     * @param string $query
     * @param int $categoryId
     * @param array $options
     * @return array
     */
    public function searchByCategory(string $query, int $categoryId, array $options = []): array
    {
        $options['filter_by'] = "category_id:=$categoryId";
        return $this->search($query, $options);
    }

    /**
     * Get search suggestions
     *
     * @param string $query
     * @return array
     */
    public function getSuggestions(string $query): array
    {
        $results = $this->autocomplete($query, 5);

        if (empty($results['hits'])) {
            return [];
        }

        return array_map(
            fn($hit) => [
                'id' => $hit['document']['id'],
                'title' => $hit['document']['title'],
                'category' => $hit['document']['category_name'],
            ],
            $results['hits']
        );
    }
}
