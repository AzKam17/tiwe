<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard_home')]
    public function index(#[CurrentUser] User $user): Response
    {
        return $this->render('dashboard/home.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/recharge', name: 'app_dashboard_recharge')]
    public function recharge(#[CurrentUser] User $user, Request $request): Response
    {
        $amount = $request->request->get('amount');
        $accountHolder = $request->request->get('account_holder');
        $transactionProof = $request->files->get('transaction_proof');

        dump($amount, $accountHolder, $transactionProof);
        return $this->redirectToRoute('app_dashboard_home', [], Response::HTTP_SEE_OTHER);
    }
}
