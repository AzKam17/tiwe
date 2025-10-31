<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProductDataFixtures extends Fixture implements DependentFixtureInterface
{
    private array $categories = [];

    public function __construct(
        private readonly ParameterBagInterface $params
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $jsonFilePath = $this->params->get('kernel.project_dir') . '/data.json';

        if (!file_exists($jsonFilePath)) {
            throw new \RuntimeException("JSON file not found: $jsonFilePath");
        }

        $jsonData = file_get_contents($jsonFilePath);
        $productsData = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        // Get the admin user to assign as product creator
        $user = $manager->getRepository(User::class)->findOneBy(['username' => 'admin']);

        if (!$user) {
            throw new \RuntimeException('Admin user not found. Please run UserFixtures first.');
        }

        foreach ($productsData as $productData) {
            $category = $this->getOrCreateCategory(
                $manager,
                $productData['category']['main'],
                $productData['category']['sub']
            );

            $product = new Product();
            $product->setTitle($productData['name']);
            $product->setDescription($productData['description']);
            $product->setMeasurementType($productData['measurement_type']['type']);
            $product->setMeasurementUnit($productData['measurement_type']['unit']);
            $product->setImages($productData['images']);
            $product->setCategory($category);
            $product->setCreatedBy($user);

            $manager->persist($product);
        }

        $manager->flush();
    }

    private function getOrCreateCategory(
        ObjectManager $manager,
        string $mainCategory,
        string $subCategory
    ): ProductCategory {
        $categoryTitle = $mainCategory . ' - ' . $subCategory;

        if (isset($this->categories[$categoryTitle])) {
            return $this->categories[$categoryTitle];
        }

        $category = $manager->getRepository(ProductCategory::class)
            ->findOneBy(['title' => $categoryTitle]);

        if (!$category) {
            $category = new ProductCategory();
            $category->setTitle($categoryTitle);
            $manager->persist($category);
        }

        $this->categories[$categoryTitle] = $category;

        return $category;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
