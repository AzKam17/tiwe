<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $roles = [
            ['name' => 'Planteur', 'value' => 'ROLE_PLANTEUR', 'description' => 'Utilisateur qui plante et entretient les cultures.'],
            ['name' => 'Coopérative', 'value' => 'ROLE_COOPERATIVE', 'description' => 'Organisation regroupant plusieurs planteurs pour la vente collective.'],
            ['name' => 'Fournisseur', 'value' => 'ROLE_FOURNISSEUR', 'description' => 'Fournit des matières premières ou des intrants aux planteurs et coopératives.'],
            ['name' => 'Revendeur', 'value' => 'ROLE_REVENDEUR', 'description' => 'Revend les produits agricoles aux marchés ou distributeurs.'],
            ['name' => 'Transformateur', 'value' => 'ROLE_TRANSFORMATEUR', 'description' => 'Transforme les matières premières en produits finis.'],
            ['name' => 'Acheteur', 'value' => 'ROLE_ACHETEUR', 'description' => 'Acheteur de produits agricoles ou transformés.'],
            ['name' => 'Admin', 'value' => 'ROLE_ADMIN', 'description' => 'Administrateur ayant tous les droits sur la plateforme.'],
        ];

        foreach ($roles as $roleData) {
            $role = new Role();
            $role->setName($roleData['name']);
            $role->setValue($roleData['value']);
            $role->setDescription($roleData['description']);

            if ($roleData['value'] === 'ROLE_ADMIN') {
                $this->setReference('role_admin', $role);
            }

            $manager->persist($role);
        }

        $manager->flush();
    }
}
