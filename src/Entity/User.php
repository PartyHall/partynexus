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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN")',
        )
    ]
)]
#[ORM\Entity]
#[ORM\Table('nexus_user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 32)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 32)]
    private string $username;

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

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'owner')]
    private Collection $events;

    public function __construct()
    {
        $this->appliances = new ArrayCollection();
        $this->events = new ArrayCollection();
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

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
        }

        return $this;
    }
}
