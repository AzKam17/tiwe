<?php

declare(strict_types=1);

namespace App\Controller\Orders;

use App\Entity\User;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/purchases')]
class AuthPurchasesController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'app_auth_purchases_home')]
    public function home(
        #[CurrentUser] User $user,
        OrderRepository $orderRepository,
    ): Response
    {
        return $this->render('dashboard/purchases/index.html.twig', [
            'orders' => $orderRepository->getMyPurchases($user)
        ]);
    }
}
