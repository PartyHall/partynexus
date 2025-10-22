<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsDoctrineListener(Events::prePersist)]
readonly class UserRegisteredListener
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        if (!$args->getObject() instanceof User) {
            return;
        }

        /** @var User $user */
        $user = $args->getObject();

        if ($user->newPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->newPassword));
            $user->newPassword = null;
        }
    }
}
