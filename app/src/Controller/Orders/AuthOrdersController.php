<?php

declare(strict_types=1);

namespace App\Controller\Orders;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Enum\OrderItemEnum;
use App\Repository\OrderRepository;
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
    public function details(Order $order, #[CurrentUser] User $user): Response
    {
        $itemsToKeep = [];
        $orderItems = $order->getItems();

        // Filter items to only show those from the current seller's inventory
        foreach ($orderItems as $item) {
            $inventoryEntry = $item->getInventoryEntry();

            // Only include items where the inventory entry belongs to the current user
            if ($inventoryEntry && $inventoryEntry->getUser()->getId() === $user->getId()) {
                $itemsToKeep[] = $item;
            }
        }

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


    #[Route('/{id}/api', name: 'app_auth_orders_details_api')]
    public function detailsApi(Order $order, #[CurrentUser] User $user): Response
    {
        $itemsToKeep = [];
        $orderItems = $order->getItems();

        // Filter items to only show those from the current seller's inventory
        foreach ($orderItems as $item) {
            $inventoryEntry = $item->getInventoryEntry();

            // Only include items where the inventory entry belongs to the current user
            if ($inventoryEntry && $inventoryEntry->getUser()->getId() === $user->getId()) {
                $itemsToKeep[] = $item;
            }
        }

        $order
            ->emptyItems()
            ->addItems($itemsToKeep)
            ->computeAmount()
            ->computeTotalAmount()
        ;

        return $this->render('dashboard/orders/_order_details_content.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/{status}', name: 'app_order_update_status')]
    public function updateStatus(Order $order, $status, #[CurrentUser] User $user, EntityManagerInterface $em): Response
    {
        $orderStatus = OrderItemEnum::tryFrom($status);
        if (!$orderStatus) {
            throw $this->createNotFoundException();
        }

        // Update status only for items from the current seller's inventory
        foreach ($order->getItems() as $item) {
            $inventoryEntry = $item->getInventoryEntry();

            // Only update items where the inventory entry belongs to the current user
            if ($inventoryEntry && $inventoryEntry->getUser()->getId() === $user->getId()) {
                $item->setStatus($orderStatus);
                $em->persist($item);
            }
        }

        $em->flush();

        return $this->redirectToRoute('app_auth_orders_details', ['id' => $order->getId()] );
    }
}
