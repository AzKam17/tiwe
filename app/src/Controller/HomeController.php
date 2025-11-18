<?php

namespace App\Controller;

use App\Repository\InventoryEntryRepository;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        ProductCategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        $search = $request->query->get('search', '');
        $categoryId = $request->query->getInt('category', 0);

        $queryBuilder = $productRepository->createQueryBuilder('p')
            ->leftJoin('p.inventoryEntries', 'ie')
            ->leftJoin('p.category', 'c')
            ->addSelect('ie')
            ->addSelect('c')
            ->orderBy('p.id', 'DESC');

        // Apply search filter
        if ($search) {
            $queryBuilder->andWhere('p.title LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply category filter
        if ($categoryId > 0) {
            $queryBuilder->andWhere('p.category = :category')
                ->setParameter('category', $categoryId);
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12 // Items per page
        );

        return $this->render('home/index.html.twig', [
            'products' => $pagination,
            'categories' => $categoryRepository->findAll(),
            'currentSearch' => $search,
            'currentCategory' => $categoryId,
        ]);
    }

    #[Route('/product/{id}/inventory-entries', name: 'app_product_inventory_entries', methods: ['GET'])]
    public function getInventoryEntries(
        int $id,
        ProductRepository $productRepository,
        InventoryEntryRepository $inventoryEntryRepository,
        Request $request
    ): JsonResponse
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        $sortBy = $request->query->get('sort', 'recent');
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = 25; // 5x5 grid

        $queryBuilder = $inventoryEntryRepository->createQueryBuilder('ie')
            ->where('ie.product = :product')
            ->andWhere('ie.quantity > 0')
            ->setParameter('product', $product)
            ->leftJoin('ie.user', 'u')
            ->addSelect('u');

        // Apply sorting
        switch ($sortBy) {
            case 'price_asc':
                $queryBuilder->orderBy('ie.price', 'ASC');
                break;
            case 'price_desc':
                $queryBuilder->orderBy('ie.price', 'DESC');
                break;
            case 'recent':
            default:
                $queryBuilder->orderBy('ie.createdAt', 'DESC');
                break;
        }

        // Get total count for pagination
        $totalCount = (int) $inventoryEntryRepository->createQueryBuilder('ie')
            ->select('COUNT(ie.id)')
            ->where('ie.product = :product')
            ->andWhere('ie.quantity > 0')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $inventoryEntries = $queryBuilder->getQuery()->getResult();

        $data = [];
        foreach ($inventoryEntries as $entry) {
            $data[] = [
                'id' => $entry->getId(),
                'quantity' => $entry->getQuantity(),
                'price' => (float)$entry->getPrice(),
                'totalPrice' => $entry->getTotalPrice(),
                'createdAt' => $entry->getCreatedAt()->format('d/m/Y H:i'),
                'notes' => $entry->getNotes(),
                'image' => $entry->getImage(),
                'user' => [
                    'id' => $entry->getUser()->getId(),
                    'name' => $entry->getUser()->getFirstName() . ' ' . $entry->getUser()->getLastName(),
                ],
            ];
        }

        $totalPages = (int) ceil($totalCount / $perPage);

        return new JsonResponse([
            'product' => [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'measurementUnit' => $product->getMeasurementUnit(),
            ],
            'entries' => $data,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalEntries' => $totalCount,
                'totalPages' => $totalPages,
                'hasNextPage' => $page < $totalPages,
                'hasPreviousPage' => $page > 1,
            ],
        ]);
    }
}
