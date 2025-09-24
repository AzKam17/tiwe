<?php

declare(strict_types=1);

namespace App\Controller\Products;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('products/index.html.twig', [
            'product' => $product,
            // 'show_cart_modal' => true
        ]);
    }
}
