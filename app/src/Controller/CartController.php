<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/cart')]
final class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index')]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig');
    }

    #[Route('/confirm', name: 'app_cart_confirm', methods: ['GET'])]
    public function confirm(
        #[CurrentUser] User $user,
        CartService $cartService,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {

        $order = new Order();
        $order->setBuyer($user);
        $cart = $cartService->getCart();

        foreach ($cart as $item) {
            /* @var Product | null $product */
            $product = $productRepository->find($item['id']);
            if(!$product) {
                continue;
            }

            $quantity = min($item['quantity'], $product->getStock());

            $product->setStock($product->getStock() - $quantity);

            $orderItem = (new OrderItem())
                ->setPrice($product->getPrice())
                ->setProduct($product)
                ->setQuantity($quantity);

            $entityManager->persist($orderItem);

            $order->addItem($orderItem);
        }

        $order->computeAmount();
        $order->setFees(0);
        $order->computeTotalAmount();
        $entityManager->persist($order);
        $entityManager->flush();

        $cartService->clearCart();

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
