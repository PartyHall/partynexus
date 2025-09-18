<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\Language;
use App\Model\PasswordSet;
use App\State\Processor\BanUserProcessor;
use App\State\Processor\RegisterUserProcessor;
use App\State\Processor\UserSetPasswordProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @TODO: GET ITEM should be PII proof
 * It should NOT tell the user email unless the SELF is set even though those endpoints are set for self & admin only
 * as they can be used in other stuff (e.g. Forgotten Password, Event listing, ...)
 *
 * I'm not doing this right now as I don't know about the side effects
 */
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            security: 'object == user or is_granted("ROLE_ADMIN")',
        ),
        new GetCollection(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COLLECTION]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Post(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Patch(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_UPDATE]],
            security: 'is_granted("ROLE_ADMIN") or user === object',
        ),
        new Post(
            uriTemplate: '/users/{id}/ban',
            status: 201,
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            security: 'is_granted("ROLE_ADMIN")',
            name: self::BAN_USER_ROUTE,
            processor: BanUserProcessor::class,
        ),
        new Post(
            uriTemplate: '/users/{id}/unban',
            status: 201,
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            security: 'is_granted("ROLE_ADMIN")',
            name: self::UNBAN_USER_ROUTE,
            processor: BanUserProcessor::class,
        ),
        new Post(
            uriTemplate: '/users/{id}/set-password',
            security: 'user === object',
            validationContext: [AbstractNormalizer::GROUPS => [PasswordSet::API_SET_PASSWORD]],
            input: PasswordSet::class,
            processor: UserSetPasswordProcessor::class,
        ),
        new Post(
            uriTemplate: '/register/{userRegistrationCode}',
            uriVariables: ['userRegistrationCode' => new Link()],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_REGISTER]],
            validationContext: [AbstractNormalizer::GROUPS => [self::API_REGISTER]],
            processor: RegisterUserProcessor::class,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['username' => 'ipartial'])]
#[UniqueEntity(fields: ['username'], message: 'Username already taken')]
#[UniqueEntity(fields: ['email'], message: 'Email already taken')]
#[UniqueEntity(fields: ['username'], message: 'Username already taken', groups: [self::API_REGISTER])]
#[UniqueEntity(fields: ['email'], message: 'Email already taken', groups: [self::API_REGISTER])]
#[ORM\Entity]
#[ORM\Table('nexus_user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const string BAN_USER_ROUTE = 'ban_user';
    public const string UNBAN_USER_ROUTE = 'unban_user';

    public const string API_GET_COLLECTION = 'api:user:get-collection';
    public const string API_GET_ITEM = 'api:user:get';
    public const string API_GET_ITEM_SELF = 'api:user:get-self';
    public const string API_CREATE = 'api:user:create';
    public const string API_UPDATE = 'api:user:update';
    public const string API_REGISTER = 'api:user:create-register';

    public const string DEFAULT_VALIDATION_GROUP = 'Default';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 32, unique: true)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9._-]{3,32}$/', groups: [self::DEFAULT_VALIDATION_GROUP, self::API_REGISTER])]
    #[Assert\Length(min: 3, max: 32, groups: [self::DEFAULT_VALIDATION_GROUP, self::API_REGISTER])]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        self::API_REGISTER,
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
        Event::API_EXPORT,
    ])]
    private string $username;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true)]
    #[Assert\Length(min: 2, max: 64, groups: [self::DEFAULT_VALIDATION_GROUP, self::API_REGISTER])]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_REGISTER,
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
        Event::API_EXPORT,
    ])]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_REGISTER,
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
        Event::API_EXPORT,
    ])]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::STRING, length: 512, nullable: true)]
    private ?string $password = null;

    /**
     * Meh, this is to clean up later, I use this for auto-registration
     * but password change is its own DTO, not sure how to do it properly
     * and I'd rather not have a custom DTO for user registration,
     * we'll see after the frontend rewrite.
     */
    #[Groups([self::API_REGISTER])]
    #[Assert\NotCompromisedPassword(message: 'validation.not_compromised', groups: [self::API_REGISTER])]
    #[Assert\NotBlank(message: 'validation.not_blank', groups: [self::API_REGISTER])]
    #[Assert\Length(min: 8, groups: [self::API_REGISTER])]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/',
        message: 'validation.password_requirements',
        groups: [self::API_REGISTER],
    )]
    public ?string $newPassword = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank(['groups' => [self::DEFAULT_VALIDATION_GROUP, self::API_REGISTER]])]
    #[Assert\Email(['groups' => [self::DEFAULT_VALIDATION_GROUP, self::API_REGISTER]])]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_REGISTER,
    ])]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: Language::class, options: ['default' => 'en_US'])]
    #[Assert\NotBlank(['groups' => [self::DEFAULT_VALIDATION_GROUP, self::API_REGISTER]])]
    #[Groups([
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
        self::API_REGISTER,
    ])]
    private Language $language;

    /** @var string[] $roles */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private ?\DateTimeImmutable $bannedAt = null;

    /** @var Collection<int, ForgottenPassword> $forgottenPasswords */
    #[ORM\OneToMany(targetEntity: ForgottenPassword::class, mappedBy: 'user', cascade: ['PERSIST'])]
    private Collection $forgottenPasswords;

    /** @var Collection<int, Appliance> $appliances */
    #[ORM\OneToMany(targetEntity: Appliance::class, mappedBy: 'owner')]
    #[Groups([
        self::API_GET_ITEM_SELF,
    ])]
    private Collection $appliances;

    /** @var Collection<int, Event> */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'owner', cascade: ['PERSIST'])]
    private Collection $userEvents;

    /** @var Collection<int, Event> */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants', cascade: ['PERSIST'])]
    private Collection $participatingEvents;

    /** @var Collection<int, SongRequest> */
    #[ORM\OneToMany(targetEntity: SongRequest::class, mappedBy: 'user')]
    private Collection $songRequests;

    /** @var Collection<int, UserAuthenticationLog> */
    #[ORM\OneToMany(targetEntity: UserAuthenticationLog::class, mappedBy: 'user')]
    private Collection $authLogs;

    public function __construct()
    {
        $this->forgottenPasswords = new ArrayCollection();
        $this->appliances = new ArrayCollection();
        $this->userEvents = new ArrayCollection();
        $this->participatingEvents = new ArrayCollection();
        $this->songRequests = new ArrayCollection();
        $this->authLogs = new ArrayCollection();
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

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
     * @return Collection<int, ForgottenPassword>
     */
    public function getForgottenPasswords(): Collection
    {
        return $this->forgottenPasswords;
    }

    public function addForgottenPassword(ForgottenPassword $link): self
    {
        if (!$this->forgottenPasswords->contains($link)) {
            $link->setUser($this);
            $this->forgottenPasswords->add($link);
        }

        return $this;
    }

    public function removeForgottenPassword(ForgottenPassword $link): self
    {
        if ($this->forgottenPasswords->contains($link)) {
            $this->forgottenPasswords->removeElement($link);
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

    public function addParticipatingEvent(Event $event): self
    {
        if (!$this->participatingEvents->contains($event)) {
            $this->participatingEvents->add($event);
            $event->addParticipant($this);
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
     * @param array<Event>|Collection<int|string, User> $participatingEvents
     */
    public function setParticipatingEvents(array|Collection $participatingEvents): self
    {
        if (\is_array($participatingEvents)) {
            $participatingEvents = new ArrayCollection($participatingEvents);
        }

        $this->participatingEvents = $participatingEvents;

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): self
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
     * @return Collection<int, SongRequest>
     */
    public function getSongRequests(): Collection
    {
        return $this->songRequests;
    }

    /**
     * @param array<SongRequest>|Collection<int, SongRequest> $songRequests
     */
    public function setSongRequests(array|Collection $songRequests): void
    {
        if (\is_array($songRequests)) {
            $songRequests = new ArrayCollection($songRequests);
        }

        $this->songRequests = $songRequests;
    }

    /** @return Collection<int, UserAuthenticationLog> */
    public function getAuthLogs(): Collection
    {
        return $this->authLogs;
    }

    /** @param array<UserAuthenticationLog>|Collection<int, UserAuthenticationLog> $authLogs */
    public function setAuthLogs(array|Collection $authLogs): self
    {
        if (\is_array($authLogs)) {
            $authLogs = new ArrayCollection($authLogs);
        }

        $this->authLogs = $authLogs;

        return $this;
    }

    public function getFullName(): string
    {
        $fullName = $this->getUsername();

        $firstName = $this->getFirstname() ?? '';
        $lastName = $this->getLastname() ?? '';

        if (!empty($firstName)) {
            $fullName = \sprintf('%s %s', $firstName, $lastName);
        }

        return $fullName;
    }

    #[Groups([self::API_GET_ITEM])]
    public function isPasswordSet(): bool
    {
        return null !== $this->password;
    }
}
