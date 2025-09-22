<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard_home')]
    public function index(): Response
    {
        return $this->render('dashboard/home.html.twig');
    }

    #[Route('/dashboard/products', name: 'app_dashboard_products')]
    public function products(): Response
    {
        return $this->render('dashboard/products.html.twig');
    }
}
