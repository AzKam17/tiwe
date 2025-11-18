<?php

// src/Service/CartService.php
namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private const CART_KEY = 'cart';
    private const SHOW_MODAL_KEY = 'showCartModal';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    public function getCart(): array
    {
        return $this->getSession()->get(self::CART_KEY, []);
    }

    public function addItem(array $item): void
    {
        $cart = $this->getCart();

        if (isset($cart[$item['id']])) {
            $cart[$item['id']]['quantity'] += $item['quantity'] ?? 1;
        } else {
            $item['quantity'] = $item['quantity'] ?? 1;
            $cart[$item['id']] = $item;
        }

        $this->getSession()->set(self::CART_KEY, $cart);
        $this->getSession()->set(self::SHOW_MODAL_KEY, true);
    }

    public function removeItem(int $id): void
    {
        $cart = $this->getCart();
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $this->getSession()->set(self::CART_KEY, $cart);
    }

    public function updateQuantity(int $id, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($id);
            return;
        }

        $cart = $this->getCart();
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $quantity;
            $this->getSession()->set(self::CART_KEY, $cart);
        }
    }

    public function incrementQuantity(int $id, int $increment = 1): void
    {
        $cart = $this->getCart();
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $increment;
            $this->getSession()->set(self::CART_KEY, $cart);
        }
    }

    public function decrementQuantity(int $id, int $decrement = 1): void
    {
        $cart = $this->getCart();
        if (isset($cart[$id])) {
            $newQuantity = $cart[$id]['quantity'] - $decrement;
            if ($newQuantity <= 0) {
                $this->removeItem($id);
            } else {
                $cart[$id]['quantity'] = $newQuantity;
                $this->getSession()->set(self::CART_KEY, $cart);
            }
        }
    }

    public function getItemQuantity(int $id): int
    {
        $cart = $this->getCart();
        return $cart[$id]['quantity'] ?? 0;
    }

    public function hasItem(int $id): bool
    {
        $cart = $this->getCart();
        return isset($cart[$id]);
    }

    public function getCartCount(): int
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }

    public function shouldShowModal(): bool
    {
        return $this->getSession()->get(self::SHOW_MODAL_KEY, false);
    }

    public function hideModal(): void
    {
        $this->getSession()->remove(self::SHOW_MODAL_KEY);
    }

    public function clearCart(): void
    {
        $this->getSession()->remove(self::CART_KEY);
        $this->hideModal();
    }
}

