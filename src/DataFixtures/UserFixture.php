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

    private function createUser(int $id, string $username, array $roles = []): void
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($username . '@partyhall.dev')
            ->setLanguage('en_US')
        ;

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        ReflectionUtils::setId($user, $id);

        $user->setPassword($this->hasher->hashPassword($user, 'password'));
        $this->manager->persist($user);
        $this->setReference('user__' . $username, $user);
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $this->createUser(1, 'admin', ['ROLE_ADMIN']);
        $this->createUser(2, 'eventmaker', ['ROLE_ADMIN']);
        $this->createUser(3, 'user');
        $this->createUser(4, 'noevents');

        $manager->flush();
    }
}
