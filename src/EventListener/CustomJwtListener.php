<?php

namespace App\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\User;
use App\Entity\UserAuthenticationLog;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEventListener(Events::JWT_CREATED, method: 'onJwtCreated')]
readonly class CustomJwtListener
{
    private const AUTHORIZATION_DETAIL_TYPE = 'https://mercure.rocks/authorization-detail';

    public function __construct(
        private IriConverterInterface $iriConverter,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        #[Autowire('%env(PUBLIC_URL)%')]
        private string $issuer,
        #[Autowire('%env(PUBLIC_URL)%/.well-known/mercure')]
        private string $audience,
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

        // Mercure 1.x access token claims (RFC 9068 / RFC 9396).
        $payload['iss'] = $this->issuer;
        $payload['aud'] = $this->audience;
        $payload['sub'] = (string) $user->getId();
        $payload['jti'] = bin2hex(random_bytes(16));
        $payload['authorization_details'] = [
            [
                'type' => self::AUTHORIZATION_DETAIL_TYPE,
                'actions' => ['subscribe'],
                'topics' => [
                    [
                        'match' => \sprintf('/users/%s?topic=*', $user->getId()),
                        'match_type' => 'urlpattern',
                    ],
                ],
            ],
        ];

        unset($payload['mercure']);

        $event->setHeader([...$event->getHeader(), 'typ' => 'at+jwt']);
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
