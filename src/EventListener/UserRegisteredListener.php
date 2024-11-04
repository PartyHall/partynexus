<?php

namespace App\EventListener;

use App\Entity\User;
use App\Message\UserRegisteredNotification;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(Events::prePersist)]
readonly class UserRegisteredListener
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        if (!$args->getObject() instanceof User) {
            return;
        }

        /** @var User $user */
        $user = $args->getObject();

        $this->messageBus->dispatch(new UserRegisteredNotification(
            $user->getLanguage(),
            $user->getUsername(),
            $user->getEmail(),
        ));
    }
}
