<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Event;
use App\Repository\EventRepository;

/**
 * @implements ProviderInterface<Event>
 */
readonly class RegistrationEventProvider implements ProviderInterface
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['userRegistrationCode'])) {
            return null;
        }

        $event = $this->eventRepository->findOneBy([
            'userRegistrationCode' => $uriVariables['userRegistrationCode'],
        ]);

        if (!$event || !$event->isUserRegistrationEnabled()) {
            return null;
        }

        return $event;
    }
}
