<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

#[ApiResource(
    operations: [
        new Get(
            security: 'object == user or is_granted("ROLE_ADMIN")'
        ),
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN")',
        )
    ]
)]
#[ORM\Entity]
#[ORM\Table('nexus_user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const string API_GET_COLLECTION = 'api:user:get-collection';
    public const string API_GET_ITEM = 'api:user:get';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true)]
    #[Assert\Length(min: 3, max: 32)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    private ?string $username = null;

    #[ORM\Column(type: Types::STRING, length: 512)] // @TODO: Check length is good enough
    private ?string $password;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /** @var Collection<Appliance> $appliances  */
    #[ORM\OneToMany(targetEntity: Appliance::class, mappedBy: 'owner')]
    private Collection $appliances;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'owner', cascade: ['PERSIST'])]
    private Collection $userEvents;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants', cascade: ['PERSIST'])]
    private Collection $participatingEvents;

    public function __construct()
    {
        $this->appliances = new ArrayCollection();
        $this->userEvents = new ArrayCollection();
        $this->participatingEvents = new ArrayCollection();
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

    /** @returns Collection<Appliance> */
    public function getAppliances(): Collection
    {
        return $this->appliances;
    }

    public function hasAppliance(UserInterface $appliance): bool
    {
        return $this->appliances->contains($appliance);
    }

    /**
     * @param Appliance[]|Collection<Appliance> $appliances
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
            $role = 'ROLE_' . $role;
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
            $role = 'ROLE_' . $role;
        }

        $this->roles = array_filter($this->roles, fn($x) => $x !== $role);

        return $this;
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return Collection<User>
     */
    public function getUserEvents(): Collection
    {
        return $this->userEvents;
    }

    /**
     * @param User[]|Collection<User> $userEvents
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
     * @return Collection<User>
     */
    public function getParticipatingEvents(): Collection
    {
        return $this->participatingEvents;
    }

    /**
     * @param User[]|Collection<User> $participatingEvents
     */
    public function setParticipatingEvents(array|Collection $participatingEvents): self
    {
        if (\is_array($participatingEvents)) {
            $participatingEvents = new ArrayCollection($participatingEvents);
        }

        $this->participatingEvents = $participatingEvents;

        return $this;
    }
}
