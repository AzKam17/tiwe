<?php

namespace App\Controller;

use App\Entity\InventoryEntry;
use App\Entity\User;
use App\Form\InventoryEntryType;
use App\Repository\ProductRepository;
use App\Service\ProductSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        SluggerInterface $slugger,
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
            // Handle the image file upload
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/inventory_entries';

                    // Create directory if it doesn't exist
                    if (!is_dir($uploadsDirectory)) {
                        mkdir($uploadsDirectory, 0777, true);
                    }

                    $imageFile->move($uploadsDirectory, $newFilename);
                    $inventoryEntry->setImage('/uploads/inventory_entries/'.$newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            $em->persist($inventoryEntry);
            $em->flush();

            $unit = $product->getMeasurementUnit() ?? 'unité';
            $this->addFlash('success', sprintf(
                'Entrée d\'inventaire enregistrée : %s %s à %.2f FCFA/%s (Total: %s FCFA)',
                $inventoryEntry->getQuantity(),
                $unit,
                (float)$inventoryEntry->getPrice(),
                $unit,
                number_format($inventoryEntry->getTotalPrice(), 2, '.', ' ')
            ));

            return $this->redirectToRoute('app_auth_products_home');
        }

        return $this->render('inventory_entry/form.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }
}
