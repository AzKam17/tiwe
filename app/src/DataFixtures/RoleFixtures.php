<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $roles = [
            ['name' => 'Planteur', 'value' => 'ROLE_PLANTEUR'],
            ['name' => 'Coopérative', 'value' => 'ROLE_COOPERATIVE'],
            ['name' => 'Fournisseur', 'value' => 'ROLE_FOURNISSEUR'],
            ['name' => 'Revendeur', 'value' => 'ROLE_REVENDEUR'],
            ['name' => 'Transformateur', 'value' => 'ROLE_TRANSFORMATEUR'],
            ['name' => 'Acheteur', 'value' => 'ROLE_ACHETEUR'],
            ['name' => 'Admin', 'value' => 'ROLE_ADMIN'],
        ];

        foreach ($roles as $roleData) {
            $role = new \App\Entity\Role();
            $role->setName($roleData['name']);
            $role->setValue($roleData['value']);

            if($roleData['value'] === 'ROLE_ADMIN') {
                $this->setReference('role_admin', $role);
            }
            $manager->persist($role);
        }

        $manager->flush();
    }
}
