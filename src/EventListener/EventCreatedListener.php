<?php

namespace App\EventListener;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Hidehalo\Nanoid\Client;

#[AsDoctrineListener(Events::prePersist)]
readonly class EventCreatedListener
{
    public function __construct(
    )
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        /** @var Event $evt */
        if (!($evt = $args->getObject()) instanceof Event) {
            return;
        }

        $nanoidGenerator = new Client();

        $evt->setUserRegistrationCode($nanoidGenerator->generateId(16));
    }
}
