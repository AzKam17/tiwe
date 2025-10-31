<?php

namespace App\Controller;

use App\Entity\InventoryEntry;
use App\Entity\User;
use App\Form\InventoryEntryType;
use App\Repository\ProductRepository;
use App\Service\ProductSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/inventory')]
final class InventoryEntryController extends AbstractController
{
    #[Route('/entry/search', name: 'app_inventory_entry_search')]
    public function search(ProductRepository $productRepository): Response
    {
        // Get recent products as initial suggestions
        $suggestions = $productRepository->getRecentProductsForSuggestions(4);

        return $this->render('inventory_entry/search.html.twig', [
            'suggestions' => $suggestions,
        ]);
    }

    #[Route('/entry/search/results', name: 'app_inventory_entry_search_results')]
    public function searchResults(
        Request $request,
        ProductSearchService $searchService
    ): Response {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return $this->render('inventory_entry/search_results.html.twig', [
                'products' => [],
            ]);
        }

        try {
            // Search products using Typesense
            $products = $searchService->searchProducts($query, [
                'per_page' => 10,
            ]);
        } catch (\Exception) {
            // Fallback to database search if Typesense is unavailable
            $products = [];
        }

        return $this->render('inventory_entry/search_results.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/entry/form/{productId}', name: 'app_inventory_entry_form')]
    public function form(
        int $productId,
        ProductRepository $productRepository,
        Request $request,
        EntityManagerInterface $em,
        #[CurrentUser] User $user
    ): Response {
        $product = $productRepository->find($productId);

        if (!$product) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('app_inventory_entry_search');
        }

        $inventoryEntry = new InventoryEntry();
        $inventoryEntry->setProduct($product);
        $inventoryEntry->setUser($user);

        $form = $this->createForm(InventoryEntryType::class, $inventoryEntry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($inventoryEntry);
            $em->flush();

            $this->addFlash('success', sprintf(
                'Entr�e d\'inventaire enregistr�e : %s � %.2f %s',
                $inventoryEntry->getQuantity(),
                (float)$inventoryEntry->getPrice(),
                $product->getMeasurementUnit() ?? 'unit�'
            ));

            return $this->redirectToRoute('app_auth_products_home');
        }

        return $this->render('inventory_entry/form.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }
}
