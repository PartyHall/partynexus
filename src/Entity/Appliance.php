<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ApplianceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @see App\Doctrine\FilterApplianceOnOwnerExtension
 */
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => [Appliance::API_GET_COLLECTION]]),
        new Get(normalizationContext: ['groups' => [Appliance::API_GET_ITEM]]),
        new Post(
            normalizationContext: ['groups' => [Appliance::API_GET_ITEM]],
            denormalizationContext: ['groups' => [Appliance::API_CREATE]],
            security: 'is_granted("ROLE_ADMIN")'
        ),
        new Put(
            normalizationContext: ['groups' => [Appliance::API_GET_ITEM]],
            denormalizationContext: ['groups' => [Appliance::API_UPDATE]],
            security: 'object.owner == user or is_granted("ROLE_ADMIN")'
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['owner' => 'exact'])]
#[ORM\Entity(repositoryClass: ApplianceRepository::class)]
class Appliance implements UserInterface
{
    public const string API_GET_COLLECTION = 'api:appliance:get-collection';
    public const string API_GET_ITEM = 'api:appliance:get';
    public const string API_CREATE = 'api:appliance:create';
    public const string API_UPDATE = 'api:appliance:update';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups([self::API_GET_COLLECTION, self::API_GET_ITEM])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 32)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 32)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
    ])]
    private string $name;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'appliances')]
    #[Groups([self::API_GET_ITEM])]
    private User $owner;

    #[ORM\Column(type: UuidType::NAME, length: 512)]
    #[Groups([self::API_GET_ITEM])]
    private Uuid $hardwareId;

    #[ORM\Column(type: Types::STRING, length: 512)]
    #[Groups([self::API_GET_ITEM])]
    private string $apiToken;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups([self::API_GET_ITEM])]
    private \DateTimeImmutable $lastSeen;

    public function __construct()
    {
        $this->hardwareId = Uuid::v4();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getHardwareId(): string
    {
        return $this->hardwareId;
    }

    public function setHardwareId(Uuid $hardwareId): self
    {
        $this->hardwareId = $hardwareId;

        return $this;
    }

    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getLastSeen(): \DateTimeImmutable
    {
        return $this->lastSeen;
    }

    public function setLastSeen(\DateTimeImmutable $lastSeen): self
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_APPLIANCE'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->hardwareId;
    }
}
