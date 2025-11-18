<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\InventoryEntryRepository;
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
        InventoryEntryRepository $inventoryEntryRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $order = new Order();
        $order->setBuyer($user);
        $cart = $cartService->getCart();

        foreach ($cart as $item) {
            // Check if this is an inventory entry or old-style product cart item
            if (isset($item['type']) && $item['type'] === 'inventory_entry') {
                // New inventory entry system
                $inventoryEntry = $inventoryEntryRepository->find($item['id']);
                if (!$inventoryEntry) {
                    continue;
                }

                $product = $inventoryEntry->getProduct();
                $requestedQuantity = $item['quantity'];
                $availableQuantity = $inventoryEntry->getQuantity();

                // Don't allow ordering more than available
                $quantity = min($requestedQuantity, $availableQuantity);

                // Deduct from inventory entry
                $inventoryEntry->setQuantity($availableQuantity - $quantity);
                $entityManager->persist($inventoryEntry);

                $orderItem = (new OrderItem())
                    ->setPrice($item['price'])
                    ->setProduct($product)
                    ->setQuantity($quantity)
                    ->setInventoryEntry($inventoryEntry);

                $entityManager->persist($orderItem);
                $order->addItem($orderItem);
            } else {
                // Legacy product-based cart system (for backward compatibility)
                $product = $productRepository->find($item['id']);
                if (!$product) {
                    continue;
                }

                $quantity = $item['quantity'];
                $price = $item['price'] ?? 0;

                $orderItem = (new OrderItem())
                    ->setPrice($price)
                    ->setProduct($product)
                    ->setQuantity($quantity);

                $entityManager->persist($orderItem);
                $order->addItem($orderItem);
            }
        }

        $order->computeAmount();
        $order->setFees(0);
        $order->computeTotalAmount();
        $entityManager->persist($order);
        $entityManager->flush();

        $cartService->clearCart();

        return $this->redirectToRoute('app_cart_index');
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

    #[Route('/api/add-inventory-entry', name: 'app_cart_add_inventory_entry', methods: ['POST'])]
    public function addInventoryEntry(
        Request $request,
        InventoryEntryRepository $inventoryEntryRepository,
        CartService $cartService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $entryId = $data['entryId'] ?? null;
        $quantity = max(1, $data['quantity'] ?? 1);

        if (!$entryId) {
            return new JsonResponse(['error' => 'Entry ID required'], 400);
        }

        $entry = $inventoryEntryRepository->find($entryId);
        if (!$entry) {
            return new JsonResponse(['error' => 'Inventory entry not found'], 404);
        }

        // Check if requested quantity is available
        if ($quantity > $entry->getQuantity()) {
            return new JsonResponse([
                'error' => 'Quantité demandée non disponible',
                'available' => $entry->getQuantity()
            ], 400);
        }

        $cartService->addItem([
            'id' => $entry->getId(),
            'type' => 'inventory_entry',
            'productId' => $entry->getProduct()->getId(),
            'productTitle' => $entry->getProduct()->getTitle(),
            'price' => (float)$entry->getPrice(),
            'quantity' => $quantity,
            'maxQuantity' => $entry->getQuantity(),
            'supplierName' => $entry->getUser()->getFirstName() . ' ' . $entry->getUser()->getLastName(),
            'image' => $entry->getImage(),
            'measurementUnit' => $entry->getProduct()->getMeasurementUnit(),
        ]);

        return new JsonResponse([
            'success' => true,
            'message' => 'Produit ajouté au panier',
            'cartCount' => $cartService->getCartCount(),
            'itemQuantity' => $cartService->getItemQuantity($entry->getId())
        ]);
    }

    #[Route('/api/update-quantity/{id}', name: 'app_cart_update_quantity', methods: ['POST'])]
    public function updateQuantity(
        int $id,
        Request $request,
        CartService $cartService,
        InventoryEntryRepository $inventoryEntryRepository
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $quantity = max(0, $data['quantity'] ?? 1);

        // Verify the quantity doesn't exceed available stock
        $entry = $inventoryEntryRepository->find($id);
        if ($entry && $quantity > $entry->getQuantity()) {
            return new JsonResponse([
                'error' => 'Quantité demandée non disponible',
                'available' => $entry->getQuantity()
            ], 400);
        }

        $cartService->updateQuantity($id, $quantity);

        return new JsonResponse([
            'success' => true,
            'cartCount' => $cartService->getCartCount(),
            'itemQuantity' => $cartService->getItemQuantity($id)
        ]);
    }

    #[Route('/api/increment/{id}', name: 'app_cart_increment', methods: ['POST'])]
    public function increment(
        int $id,
        CartService $cartService,
        InventoryEntryRepository $inventoryEntryRepository
    ): JsonResponse
    {
        // Check if we can increment
        $entry = $inventoryEntryRepository->find($id);
        $currentQuantity = $cartService->getItemQuantity($id);

        if ($entry && $currentQuantity >= $entry->getQuantity()) {
            return new JsonResponse([
                'error' => 'Quantité maximale atteinte',
                'available' => $entry->getQuantity()
            ], 400);
        }

        $cartService->incrementQuantity($id);

        return new JsonResponse([
            'success' => true,
            'cartCount' => $cartService->getCartCount(),
            'itemQuantity' => $cartService->getItemQuantity($id)
        ]);
    }

    #[Route('/api/decrement/{id}', name: 'app_cart_decrement', methods: ['POST'])]
    public function decrement(int $id, CartService $cartService): JsonResponse
    {
        $cartService->decrementQuantity($id);

        return new JsonResponse([
            'success' => true,
            'cartCount' => $cartService->getCartCount(),
            'itemQuantity' => $cartService->getItemQuantity($id)
        ]);
    }

    #[Route('/api/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(int $id, CartService $cartService): JsonResponse
    {
        $cartService->removeItem($id);

        return new JsonResponse([
            'success' => true,
            'cartCount' => $cartService->getCartCount()
        ]);
    }

    #[Route('/api/get-cart', name: 'app_cart_get', methods: ['GET'])]
    public function getCart(CartService $cartService): JsonResponse
    {
        return new JsonResponse([
            'cart' => $cartService->getCart(),
            'count' => $cartService->getCartCount()
        ]);
    }
}
