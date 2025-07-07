<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Controller\EventConcludeController;
use App\Repository\EventRepository;
use App\State\Processor\EventCreationProcessor;
use App\State\Provider\ExportDownloadProvider;
use App\State\Provider\RegistrationEventProvider;
use App\State\Provider\TimelapseDownloadProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see \App\Doctrine\FilterEventOnOwnerExtension
 */
#[ApiResource(
    operations: [
        new GetCollection(
            order: ['datetime' => 'DESC'],
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COLLECTION]],
        ),
        new Get(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            security: 'is_granted("ROLE_ADMIN") or object.getOwner().hasAppliance(user) or object.hasParticipant(user)'
        ),
        new Post(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            security: 'is_granted("ROLE_APPLIANCE") or is_granted("ROLE_ADMIN")',
            processor: EventCreationProcessor::class,
        ),
        new Patch(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_UPDATE]],
            security: 'is_granted("ROLE_ADMIN") or user == object.getOwner()'
        ),
        new Post(
            uriTemplate: '/events/{id}/conclude',
            status: 200,
            controller: EventConcludeController::class,
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => []],
            // security: 'is_granted("ROLE_ADMIN") or user == object.getOwner()', // Handled in the controller temporarly
            read: false,
            validate: false,
        ),
        new Get(uriTemplate: '/events/{id}/timelapse', provider: TimelapseDownloadProvider::class),
        new Get(uriTemplate: '/events/{id}/export', provider: ExportDownloadProvider::class),
        new Get(
            uriTemplate: '/register/{userRegistrationCode}',
            uriVariables: ['userRegistrationCode'],
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_REGISTER]],
            provider: RegistrationEventProvider::class,
        )
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'ipartial'])]
#[ORM\Entity(repositoryClass: EventRepository::class)]
#[QueryParameter('mine')]
class Event
{
    public const string API_GET_COLLECTION = 'api:event:get-collection';
    public const string API_GET_ITEM = 'api:event:get';
    public const string API_CREATE = 'api:event:create';
    public const string API_UPDATE = 'api:event:update';
    public const string API_EXPORT = 'api:export';

    public const string API_GET_REGISTER = 'api:event:get-register';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_GET_REGISTER,
        self::API_EXPORT,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_REGISTER,
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

    /** @var Collection<int, Picture> $pictures */
    #[ORM\OneToMany(targetEntity: Picture::class, mappedBy: 'event')]
    private Collection $pictures;

    #[ORM\OneToOne(targetEntity: Export::class, mappedBy: 'event')]
    #[Groups([
        self::API_GET_ITEM,
    ])]
    private ?Export $export = null;

    /** @var Collection<int, User> $participants */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'participatingEvents')]
    #[Groups([
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_EXPORT,
    ])]
    private Collection $participants;

    /** @var Collection<int, SongSession> */
    #[ORM\OneToMany(targetEntity: SongSession::class, mappedBy: 'event')]
    #[Groups([
        self::API_EXPORT,
    ])]
    private Collection $songSessions;

    #[ORM\OneToOne(targetEntity: DisplayBoardKey::class, mappedBy: 'event')]
    #[Groups([self::API_GET_ITEM])]
    private ?DisplayBoardKey $displayBoardKey = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    private ?string $userRegistrationCode = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([self::API_GET_ITEM, self::API_CREATE, self::API_UPDATE])]
    private bool $userRegistrationEnabled = false;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->songSessions = new ArrayCollection();
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

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    /**
     * @param array<Picture>|Collection<int, Picture> $pictures
     */
    public function setPictures(array|Collection $pictures): self
    {
        if (\is_array($pictures)) {
            $pictures = new ArrayCollection($pictures);
        }

        $this->pictures = $pictures;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
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
     * @param array<User>|Collection<int, User> $participants
     */
    public function setParticipants(array|Collection $participants): self
    {
        if (\is_array($participants)) {
            $participants = new ArrayCollection($participants);
        }

        $this->participants = $participants;

        return $this;
    }

    public function addParticipant(User $user): self
    {
        if (!$this->participants->contains($user)) {
            $this->participants->add($user);
            $user->addParticipatingEvent($this);
        }

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

    /**
     * @return Collection<int, SongSession>
     */
    public function getSongSessions(): Collection
    {
        return $this->songSessions;
    }

    public function getDisplayBoardKey(): ?DisplayBoardKey
    {
        return $this->displayBoardKey;
    }

    public function setDisplayBoardKey(?DisplayBoardKey $displayBoardKey): self
    {
        $this->displayBoardKey = $displayBoardKey;

        return $this;
    }

    public function getUserRegistrationCode(): ?string
    {
        return $this->userRegistrationCode;
    }

    public function setUserRegistrationCode(?string $userRegistrationCode): self
    {
        $this->userRegistrationCode = $userRegistrationCode;

        return $this;
    }

    public function isUserRegistrationEnabled(): bool
    {
        return $this->userRegistrationEnabled;
    }

    public function setUserRegistrationEnabled(bool $userRegistrationEnabled): self
    {
        $this->userRegistrationEnabled = $userRegistrationEnabled;

        return $this;
    }
}
