<?php

// src/Service/CartService.php
namespace App\Service;

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

