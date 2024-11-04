<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\EventConcludeController;
use App\Repository\EventRepository;
use App\State\Processor\EventCreationProcessor;
use App\State\Provider\ExportDownloadProvider;
use App\State\Provider\TimelapseDownloadProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see App\Doctrine\FilterEventOnOwnerExtension
 */
#[ApiResource(
    operations: [
        new GetCollection(
            order: ['datetime' => 'DESC'],
            normalizationContext: ['groups' => [self::API_GET_COLLECTION]],
        ),
        new Get(
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            security: 'is_granted("ROLE_ADMIN") or object.getOwner().hasAppliance(user) or object.hasParticipant(user)'
        ),
        new Post(
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            denormalizationContext: ['groups' => [self::API_CREATE]],
            security: 'is_granted("ROLE_APPLIANCE") or is_granted("ROLE_ADMIN")',
            processor: EventCreationProcessor::class,
        ),
        new Patch(
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            denormalizationContext: ['groups' => [self::API_UPDATE]],
            security: 'is_granted("ROLE_ADMIN") or user == object.getOwner()'
        ),
        new Post(
            uriTemplate: '/events/{id}/conclude',
            controller: EventConcludeController::class,
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            denormalizationContext: ['groups' => []],
            // security: 'is_granted("ROLE_ADMIN") or user == object.getOwner()', // Handled in the controller temporarly
            read: false,
            validate: false,
        ),
        new Get(uriTemplate: '/events/{id}/timelapse', provider: TimelapseDownloadProvider::class),
        new Get(uriTemplate: '/events/{id}/export', provider: ExportDownloadProvider::class),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'ipartial'])]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    public const string API_GET_COLLECTION = 'api:event:get-collection';
    public const string API_GET_ITEM = 'api:event:get';
    public const string API_CREATE = 'api:event:create';
    public const string API_UPDATE = 'api:event:update';
    public const string API_EXPORT = 'api:export';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_EXPORT,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_EXPORT,
    ])]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_EXPORT,
    ])]
    private ?string $author = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_EXPORT,
    ])]
    #[Assert\NotBlank]
    private \DateTimeImmutable $datetime;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_EXPORT,
    ])]
    private ?string $location = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_EXPORT,
    ])]
    private User $owner;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::API_GET_ITEM,
    ])]
    private bool $over = false;

    #[ORM\OneToMany(targetEntity: Picture::class, mappedBy: 'event')]
    private Collection $pictures;

    #[ORM\OneToOne(targetEntity: Export::class, mappedBy: 'event')]
    #[Groups([
        self::API_GET_ITEM,
    ])]
    private ?Export $export = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'participatingEvents')]
    #[Groups([
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_EXPORT,
    ])]
    private Collection $participants;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

    public function getId(): Uuid
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getDatetime(): \DateTimeImmutable
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeImmutable $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $user): self
    {
        $this->owner = $user;
        $this->owner->addUserEvent($this);

        return $this;
    }

    public function isOver(): bool
    {
        return $this->over;
    }

    public function setOver(bool $over): self
    {
        $this->over = $over;

        return $this;
    }

    /** @return Collection<Picture> */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    /**
     * @param Picture[]|Collection<Picture> $pictures
     */
    public function setPictures(array|Collection $pictures): void
    {
        if (\is_array($pictures)) {
            $pictures = new ArrayCollection($pictures);
        }

        $this->pictures = $pictures;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function hasParticipant(UserInterface $user): bool
    {
        if ($this->getOwner() === $user) {
            return true;
        }

        return $this->participants->contains($user);
    }

    /**
     * @param User[]|Collection<User> $participants
     */
    public function setParticipants(array|Collection $participants): self
    {
        if (\is_array($participants)) {
            $participants = new ArrayCollection($participants);
        }

        $this->participants = $participants;

        return $this;
    }

    public function getExport(): ?Export
    {
        return $this->export;
    }

    public function setExport(?Export $export): void
    {
        $this->export = $export;
    }
}
