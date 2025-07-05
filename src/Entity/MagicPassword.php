<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Model\PasswordSet;
use App\Repository\MagicPasswordRepository;
use App\State\Processor\MagicPasswordProcessor;
use App\State\Processor\MagicPasswordSetProcessor;
use App\State\Provider\MagicPasswordProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[ApiResource(
    operations: [
        new Post(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            security: 'is_granted("ROLE_ADMIN")',
            processor: MagicPasswordProcessor::class,
        ),
        new Get(
            uriTemplate: '/magic_passwords/{code}/is-valid',
            uriVariables: ['code'],
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM, User::API_GET_ITEM]],
            provider: MagicPasswordProvider::class
        ),
        new Post(
            uriTemplate: '/magic_passwords/{code}/set-password',
            uriVariables: ['code'],
            input: PasswordSet::class,
            processor: MagicPasswordSetProcessor::class,
        ),
    ],
)]
#[ORM\Entity(repositoryClass: MagicPasswordRepository::class)]
class MagicPassword
{
    public const string API_GET_ITEM = 'api:magic_password:get';
    public const string API_CREATE = 'api:magic_password:post';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups([
        self::API_GET_ITEM,
    ])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'magicPasswords')]
    #[Groups([
        self::API_GET_ITEM,
        self::API_CREATE,
    ])]
    private User $user;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, length: 255)]
    #[Groups([
        self::API_GET_ITEM,
    ])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_GET_ITEM,
    ])]
    private string $code;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups([
        self::API_GET_ITEM,
    ])]
    private bool $used;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function setUsed(bool $used): self
    {
        $this->used = $used;

        return $this;
    }
}
