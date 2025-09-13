<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $password = $this->passwordHasher->hashPassword(
            $user,
            'admin'
        );


        $user->setUsername('admin');
        $user->setEmail('azizkamadou@gmail.com');
        $user->setPassword($password);

        $manager->persist($user);
        $manager->flush();
    }
}
