<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
final class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig');
    }

    #[Route('/add/{id}', name: 'app_cart_add')]
    public function add(Product $product, CartService $cartService, Request $request): RedirectResponse
    {
        // Add item to cart
        $cartService->addItem([
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'price' => 0,
            'category' => $product->getCategory()?->getTitle(),
        ]);

        // Redirect back to the referring page, fallback to homepage
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_product_show', ['id' => $product->getId()]));
    }
}
