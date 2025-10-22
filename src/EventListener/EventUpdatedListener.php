<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Entity\Export;
use App\Enum\EnumApiConfig;
use App\Service\Mercure;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsEntityListener(event: 'postUpdate', method: 'onUpdate', entity: Event::class)]
#[AsEntityListener(event: 'postPersist', method: 'onUpdate', entity: Export::class)]
#[AsEntityListener(event: 'postUpdate', method: 'onUpdate', entity: Export::class)]
readonly class EventUpdatedListener
{
    public function __construct(
        private Mercure $mercure,
    ) {
    }

    /**
     * When an event is updated or an export is created or updated,
     * we want to send the updated event to the user.
     */
    public function onUpdate(Event|Export $event): void
    {
        if ($event instanceof Export) {
            $event = $event->getEvent();
        }

        $this->mercure->submitToUsers(
            \sprintf('/events/%s', $event->getId()),
            $event,
            [Event::API_GET_ITEM, EnumApiConfig::GET],
            \array_map(
                fn ($user) => $user->getId(),
                [$event->getOwner(), ...$event->getParticipants()->toArray()],
            ),
        );
    }
}
