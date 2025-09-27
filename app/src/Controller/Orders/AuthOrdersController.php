<?php

declare(strict_types=1);

namespace App\Controller\Orders;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\OrderItemEnum;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/dashboard/orders')]
class AuthOrdersController extends AbstractController
{
    #[Route('/', name: 'app_auth_orders_home')]
    public function index(#[CurrentUser] User $user, OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->getOrdersForSeller($user);

        return $this->render('dashboard/orders/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'app_auth_orders_details')]
    public function details(Order $order, #[CurrentUser] User $user, ProductRepository $productRepository): Response
    {
        $itemsToKeep = [];
        $orderItems = $order->getItems();

        $buyersProductsId = array_map(function (Product $product) {
            return $product->getId();
        }, $productRepository->getOrderProducts($order, $user));

        array_map(function (OrderItem $item) use ($buyersProductsId, &$itemsToKeep)  {
            if (in_array($item->getProduct()->getId(), $buyersProductsId, true)) {
                $itemsToKeep[] = $item;
            }
        }, $orderItems->toArray());

        $order
            ->emptyItems()
            ->addItems($itemsToKeep)
            ->computeAmount()
            ->computeTotalAmount()
        ;

        return $this->render('dashboard/orders/details.html.twig', [
            'order' => $order,
        ]);
    }


    #[Route('/{id}/{status}', name: 'app_order_update_status')]
    public function updateStatus(Order $order, $status, #[CurrentUser] User $user, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $orderStatus = OrderItemEnum::tryFrom($status);
        if (!$orderStatus) {
            throw $this->createNotFoundException();
        }

        $sellerProductsId = array_map(function (Product $product) {
            return $product->getId();
        }, $productRepository->getOrderProducts($order, $user));

        foreach ($order->getItems() as $item) {
            if (in_array($item->getProduct()->getId(), $sellerProductsId, true)) {
                $item->setStatus($orderStatus);
                $em->persist($item);
            }
        }

        $em->flush();

        return $this->redirectToRoute('app_auth_orders_details', ['id' => $order->getId()] );
    }
}
