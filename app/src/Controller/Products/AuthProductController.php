<?php

declare(strict_types=1);

namespace App\Controller\Products;

use App\Entity\Product;
use App\Entity\User;
use App\Form\NewProductType;
use App\Repository\ProductCategoryRepository;
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
    public function products(#[CurrentUser] User $user, ProductRepository $repository, ProductCategoryRepository $productCategoryRepository): Response
    {
        $products = $repository->findBy(['createdBy' => $user], []);
        return $this->render('dashboard/products.html.twig', [
            'products' => $products,
            'categories' => $productCategoryRepository->findAll(),
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

    #[Route('/update/{id}', name: 'app_auth_products_update')]
    public function update(
        Product $product,
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $em,
        ProductCategoryRepository $categoryRepository
    ): Response
    {
        $form = $request->request->all();

        $categoryId = intval($form['category']);
        if ($categoryId !== $product->getCategory()->getId()) {
            $category = $categoryRepository->find($categoryId);
            if ($category) {
                $product->setCategory($category);
            }
        }

        $product
            ->setTitle($form['title'])
            ->setPrice(intval($form['price']))
            ->setStock(intval($form['stock']))
            ->setDescription($form['description']);

        $em->persist($product);
        $em->flush();

        $this->addFlash('success', 'Inventaire mise à jour avec succès.');

        return $this->redirectToRoute('app_auth_products_home', []);
    }
}
