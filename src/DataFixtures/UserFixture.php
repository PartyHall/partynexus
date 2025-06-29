<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private ObjectManager $manager;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    /**
     * @param string[] $roles
     */
    private function createUser(int $id, string $username, array $roles = [], ?string $firstname = null, ?string $lastname = null): void
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($username.'@partyhall.dev')
            ->setLanguage(Language::AMERICAN_ENGLISH)
            ->setFirstname($firstname)
            ->setLastname($lastname)
        ;

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        ReflectionUtils::setId($user, $id);

        $user->setPassword($this->hasher->hashPassword($user, 'password'));
        $this->manager->persist($user);
        $this->setReference('user__'.$username, $user);
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        /*
         * At least one user with full name, one with first name only, and one without name at all
         * to test the display on the frontend
         */

        $this->createUser(1, 'admin', ['ROLE_ADMIN'], 'Dominick', 'Cobb');
        $this->createUser(2, 'eventmaker', ['ROLE_EVENT_MAKER'], 'Robert', 'Fischer');
        $this->createUser(3, 'user');
        $this->createUser(4, 'noevents', [], 'Nash');

        $manager->flush();
    }
}
