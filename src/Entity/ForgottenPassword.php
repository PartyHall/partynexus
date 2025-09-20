<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Model\PasswordReset;
use App\Model\PasswordSet;
use App\Repository\ForgottenPasswordRepository;
use App\State\Processor\ForgottenPassword\AdminForgottenPasswordProcessor;
use App\State\Processor\ForgottenPassword\ForgottenPasswordProcessor;
use App\State\Processor\ForgottenPassword\ForgottenPasswordSetProcessor;
use App\State\Provider\ForgottenPasswordProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[ApiResource(
    operations: [
        new Post(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_NO_INFO]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            input: PasswordReset::class,
            processor: ForgottenPasswordProcessor::class,
        ),
        new Post(
            uriTemplate: '/user_forgotten_passwords',
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COMPLETE]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            security: 'is_granted("ROLE_ADMIN")',
            processor: AdminForgottenPasswordProcessor::class,
        ),
        new Get(
            uriTemplate: '/forgotten_passwords/{code}/is-valid',
            uriVariables: ['code'],
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COMPLETE, User::API_GET_ITEM]],
            provider: ForgottenPasswordProvider::class
        ),
        new Post(
            uriTemplate: '/forgotten_passwords/{code}/set-password',
            uriVariables: ['code'],
            validationContext: [AbstractNormalizer::GROUPS => [PasswordSet::API_FORGOTTEN_PASSWORD]],
            input: PasswordSet::class,
            processor: ForgottenPasswordSetProcessor::class,
        ),
    ],
)]
#[ORM\Entity(repositoryClass: ForgottenPasswordRepository::class)]
class ForgottenPassword
{
    public const string API_GET_NO_INFO = 'api:forgotten_password:get-noinfo';
    public const string API_GET_COMPLETE = 'api:forgotten_password:get-complete';
    public const string API_CREATE = 'api:forgotten_password:post';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups([
        self::API_GET_NO_INFO,
        self::API_GET_COMPLETE,
    ])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'forgottenPasswords')]
    #[Groups([
        self::API_GET_COMPLETE,
        self::API_CREATE,
    ])]
    private User $user;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, length: 255)]
    #[Groups([
        self::API_GET_NO_INFO,
    ])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_GET_NO_INFO,
    ])]
    private string $code;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups([
        self::API_GET_NO_INFO,
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
