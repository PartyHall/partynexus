<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Interface\HasEvent;
use App\Repository\DisplayBoardKeyRepository;
use App\Security\EventOwnerVoter;
use App\State\Processor\DisplayBoardKeyPostProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[ApiResource(
    operations: [
        new Get(),
        new Post(
            denormalizationContext: [
                AbstractNormalizer::GROUPS => [DisplayBoardKey::API_CREATE],
            ],
            processor: DisplayBoardKeyPostProcessor::class,
        ),
        new Delete(),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => [self::API_GET_ITEM],
    ],
    security: 'is_granted("'.EventOwnerVoter::OWNER.'")',
)]
#[ORM\Entity(repositoryClass: DisplayBoardKeyRepository::class)]
#[UniqueEntity(['event'])]
class DisplayBoardKey implements HasEvent, UserInterface
{
    public const string API_GET_ITEM = 'api:displayboardkey:get';
    public const string API_CREATE = 'api:displayboardkey:create';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups([self::API_GET_ITEM, Event::API_GET_ITEM])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 512)]
    #[Groups([self::API_GET_ITEM, Event::API_GET_ITEM])]
    private string $key;

    #[ORM\OneToOne(targetEntity: Event::class, inversedBy: 'displayBoardKey')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::API_GET_ITEM, self::API_CREATE])]
    private Event $event;

    public function getId(): int
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_DISPLAY_BOARD'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getKey();
    }
}
