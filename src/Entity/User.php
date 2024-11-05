<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Processor\BanUserProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            security: 'object == user or is_granted("ROLE_ADMIN")',
        ),
        new GetCollection(
            normalizationContext: ['groups' => [self::API_GET_COLLECTION]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Post(
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            denormalizationContext: ['groups' => [self::API_CREATE]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Patch(
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            denormalizationContext: ['groups' => [self::API_UPDATE]],
            security: 'is_granted("ROLE_ADMIN") or user === object',
        ),
        new Post(
            uriTemplate: '/users/{id}/ban',
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            security: 'is_granted("ROLE_ADMIN")',
            name: self::BAN_USER_ROUTE,
            processor: BanUserProcessor::class,
        ),
        new Post(
            uriTemplate: '/users/{id}/unban',
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            security: 'is_granted("ROLE_ADMIN")',
            name: self::UNBAN_USER_ROUTE,
            processor: BanUserProcessor::class,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['username' => 'ipartial'])]
#[UniqueEntity(fields: ['username'], message: 'Username already taken')]
#[UniqueEntity(fields: ['email'], message: 'Email already taken')]
#[ORM\Entity]
#[ORM\Table('nexus_user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const string BAN_USER_ROUTE = 'ban_user';
    public const string UNBAN_USER_ROUTE = 'unban_user';

    public const string API_GET_COLLECTION = 'api:user:get-collection';
    public const string API_GET_ITEM = 'api:user:get';
    public const string API_CREATE = 'api:user:create';
    public const string API_UPDATE = 'api:user:update';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 32, unique: true)]
    #[Assert\Length(min: 3, max: 32)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        self::API_UPDATE,
        Event::API_GET_ITEM,
        Event::API_EXPORT,
    ])]
    private string $username;

    #[ORM\Column(type: Types::STRING, length: 512, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        self::API_UPDATE,
    ])]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => 'en_US'])]
    #[Assert\NotBlank]
    #[Groups([
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
    ])]
    private string $language;

    /** @var string[] $roles */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private ?\DateTimeImmutable $bannedAt = null;

    /** @var Collection<int, MagicLink> $magicLinks */
    #[ORM\OneToMany(targetEntity: MagicLink::class, mappedBy: 'user', cascade: ['PERSIST'])]
    private Collection $magicLinks;

    /** @var Collection<int, Appliance> $appliances */
    #[ORM\OneToMany(targetEntity: Appliance::class, mappedBy: 'owner')]
    private Collection $appliances;

    /** @var Collection<int, Event> */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'owner', cascade: ['PERSIST'])]
    private Collection $userEvents;

    /** @var Collection<int, Event> */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants', cascade: ['PERSIST'])]
    private Collection $participatingEvents;

    #[ORM\OneToMany(targetEntity: SongRequest::class, mappedBy: 'user')]
    private Collection $songRequests;

    public function __construct()
    {
        $this->magicLinks = new ArrayCollection();
        $this->appliances = new ArrayCollection();
        $this->userEvents = new ArrayCollection();
        $this->participatingEvents = new ArrayCollection();
        $this->songRequests = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, MagicLink>
     */
    public function getMagicLinks(): Collection
    {
        return $this->magicLinks;
    }

    public function addMagicLink(MagicLink $link): self
    {
        if (!$this->magicLinks->contains($link)) {
            $link->setUser($this);
            $this->magicLinks->add($link);
        }

        return $this;
    }

    public function removeMagicLink(MagicLink $link): self
    {
        if ($this->magicLinks->contains($link)) {
            $this->magicLinks->removeElement($link);
        }

        return $this;
    }

    /**
     * @return Collection<int, Appliance>
     */
    public function getAppliances(): Collection
    {
        return $this->appliances;
    }

    public function hasAppliance(UserInterface $appliance): bool
    {
        return $this->appliances->contains($appliance);
    }

    /**
     * @param array<Appliance>|Collection<int, Appliance> $appliances
     */
    public function setAppliances(array|Collection $appliances): void
    {
        if (\is_array($appliances)) {
            $appliances = new ArrayCollection($appliances);
        }

        $this->appliances = $appliances;
    }

    public function getRoles(): array
    {
        return array_merge(['ROLE_USER', ...$this->roles]);
    }

    public function addRole(string $role): self
    {
        $role = strtoupper($role);

        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_'.$role;
        }

        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        $role = strtoupper($role);

        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_'.$role;
        }

        $this->roles = array_filter($this->roles, fn ($x) => $x !== $role);

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getUserEvents(): Collection
    {
        return $this->userEvents;
    }

    /**
     * @param array<Event>|Collection<int, Event> $userEvents
     */
    public function setUserEvents(array|Collection $userEvents): self
    {
        if (\is_array($userEvents)) {
            $userEvents = new ArrayCollection($userEvents);
        }

        $this->userEvents = $userEvents;

        return $this;
    }

    public function addUserEvent(Event $event): self
    {
        if (!$this->userEvents->contains($event)) {
            $this->userEvents->add($event);
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getParticipatingEvents(): Collection
    {
        return $this->participatingEvents;
    }

    /**
     * @param array<Event>|Collection<int, User> $participatingEvents
     */
    public function setParticipatingEvents(array|Collection $participatingEvents): self
    {
        if (\is_array($participatingEvents)) {
            $participatingEvents = new ArrayCollection($participatingEvents);
        }

        $this->participatingEvents = $participatingEvents;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getBannedAt(): ?\DateTimeImmutable
    {
        return $this->bannedAt;
    }

    public function setBannedAt(?\DateTimeImmutable $bannedAt): void
    {
        $this->bannedAt = $bannedAt;
    }

    /**
     * @return Collection<SongRequest>
     */
    public function getSongRequests(): Collection
    {
        return $this->songRequests;
    }

    /**
     * @param SongRequest[]|Collection<SongRequest> $songRequests
     */
    public function setSongRequests(array|Collection $songRequests): void
    {
        if (\is_array($songRequests)) {
            $songRequests = new ArrayCollection($songRequests);
        }

        $this->songRequests = $songRequests;
    }
}
