<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/users/{userId}/auth-logs/{id}',
            uriVariables: [
                'userId' => new Link(toProperty: 'user', fromClass: User::class),
                'id' => new Link(fromProperty: 'id'),
            ],
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
        ),
        new GetCollection(
            uriTemplate: '/users/{userId}/auth-logs',
            uriVariables: [
                'userId' => new Link(toProperty: 'user', fromClass: User::class),
            ],
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COLLECTION]]
        ),
    ],
    security: 'is_granted("ROLE_ADMIN")'
)]
#[ORM\Entity]
class UserAuthenticationLog
{
    public const string API_GET_COLLECTION = 'api:auth_log:get_collection';
    public const string API_GET_ITEM = 'api:auth_log:get';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'authLogs')]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private User $user;

    // ipv6 is 39 chars
    // nullable for dev, in prod you should ALWAYS set the X-Forwarded-For header
    #[ORM\Column(type: Types::STRING, length: 40, nullable: true)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private ?string $ip;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private \DateTimeImmutable $authedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getAuthedAt(): \DateTimeImmutable
    {
        return $this->authedAt;
    }

    public function setAuthedAt(\DateTimeImmutable $authedAt): self
    {
        $this->authedAt = $authedAt;

        return $this;
    }
}
