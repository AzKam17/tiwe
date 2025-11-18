<?php

namespace App\Controller;

use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
