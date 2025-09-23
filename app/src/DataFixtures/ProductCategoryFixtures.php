<?php

namespace App\DataFixtures;

use App\Entity\ProductCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Fruits & Légumes',
            'Pépinières & Semis',
            'Sol & Engrais',
            'Insecticides & Pesticides',
        ];

        foreach ($categories as $categoryName) {
            $category = new ProductCategory();
            $category->setTitle($categoryName);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
