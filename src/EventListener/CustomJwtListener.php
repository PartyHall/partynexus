<?php

namespace App\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(Events::JWT_CREATED, method: 'onJwtCreated')]
readonly class CustomJwtListener
{
    public function __construct(
        private IriConverterInterface $iriConverter,
    ) {
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        $payload = $event->getData();

        $payload['iri'] = $this->iriConverter->getIriFromResource($user);

        $event->setData($payload);
    }
}
