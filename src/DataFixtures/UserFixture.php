<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private ObjectManager $manager;
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    private function createUser(string $username, array $roles = [])
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($username . '@partyhall.dev')
        ;

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $user->setPassword($this->hasher->hashPassword($user, 'password'));
        $this->manager->persist($user);
        $this->setReference('user__' . $username, $user);
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $this->createUser('admin', ['ROLE_ADMIN']);
        $this->createUser('eventmaker', ['ROLE_ADMIN']);
        $this->createUser('user');
        $this->createUser('noevents');

        $manager->flush();
    }
}
