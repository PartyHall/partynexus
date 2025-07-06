<?php

namespace App\EventListener;

use App\Entity\MagicLink;
use App\Entity\User;
use App\Message\UserRegisteredNotification;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\RequestContextProvider;

#[AsDoctrineListener(Events::prePersist)]
readonly class UserRegisteredListener
{
    public function __construct(
        private MessageBusInterface $messageBus,
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
        } else {
            $link = (new MagicLink())
                ->setCode(\bin2hex(\random_bytes(64)))
                ->setUsed(false);

            $user->addMagicLink($link);

            $this->messageBus->dispatch(new UserRegisteredNotification(
                $user,
                $link->getCode(),
            ));
        }
    }
}
