<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $roleAdmin = $this->getReference('role_admin', Role::class);
        $user = new User();
        $password = $this->passwordHasher->hashPassword(
            $user,
            'admin'
        );

        $user->setUsername('admin');
        $user->setFirstName('Aziz');
        $user->setLastName('KAMAGATE');
        $user->setEmail('aziz@aziz.com');
        $user->setPassword($password);
        $user->addAttachedRole($roleAdmin);

        $manager->persist($user);
        $manager->flush();

        $company = Company::fromUser($user);
        $manager->persist($company);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RoleFixtures::class,
        ];
    }
}
