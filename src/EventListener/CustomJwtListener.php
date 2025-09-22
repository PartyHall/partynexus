<?php

namespace App\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\User;
use App\Entity\UserAuthenticationLog;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEventListener(Events::JWT_CREATED, method: 'onJwtCreated')]
readonly class CustomJwtListener
{
    public function __construct(
        private IriConverterInterface $iriConverter,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            throw new \Exception('User is not a user');
        }

        $payload = $event->getData();

        $payload['iri'] = $this->iriConverter->getIriFromResource($user);
        $payload['username'] = $user->getUsername();
        $payload['email'] = $user->getEmail();
        $payload['id'] = $user->getId();
        $payload['language'] = $user->getLanguage();
        $payload['mercure'] = [
            'publish' => [],
            'subscribe' => [
                \sprintf('/users/%s{?topic}', $user->getId()),
            ],
        ];

        $event->setData($payload);

        $log = (new UserAuthenticationLog())
            ->setUser($user)
            ->setAuthedAt(new \DateTimeImmutable())
            ->setIp($this->requestStack->getMainRequest()?->headers->get('X-Forwarded-For'))
        ;

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
