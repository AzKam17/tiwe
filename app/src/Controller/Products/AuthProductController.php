<?php

declare(strict_types=1);

namespace App\Controller\Products;

use App\Entity\Product;
use App\Entity\User;
use App\Form\NewProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/dashboard/products')]
class AuthProductController extends AbstractController
{
    #[Route('/', name: 'app_auth_products_home')]
    public function products(#[CurrentUser] User $user,ProductRepository $repository): Response
    {
        $products = $repository->findBy(['createdBy' => $user], []);
        dump($products);
        return $this->render('dashboard/products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'app_auth_products_new')]
    public function index(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $product->setCreatedBy($user);
        $form = $this->createForm(NewProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Récolte ajoutée avec succès.');
            return $this->redirectToRoute('app_auth_products_home', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('dashboard/products/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
