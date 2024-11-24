<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Interface\HasEvent;
use App\Security\EventVoter;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            security: 'is_granted("'.EventVoter::PARTICIPANT.'", object)',
        ),
        new GetCollection(
            uriTemplate: '/events/{eventId}/song-sessions',
            uriVariables: [
                'eventId' => new Link(
                    fromProperty: 'songSessions',
                    fromClass: Event::class,
                ),
            ],
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COLLECTION]],
        ),
        new Post(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COLLECTION]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            security: 'is_granted("ROLE_APPLIANCE")',
        ),
    ],
)]
class SongSession implements HasEvent
{
    public const string API_GET_ITEM = 'api:song_session:get';
    public const string API_GET_COLLECTION = 'api:song_session:get-collection';
    public const string API_CREATE = 'api:song_session:create';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        Event::API_EXPORT,
    ])]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        Event::API_EXPORT,
    ])]
    #[Assert\NotBlank]
    private string $artist;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'songSessions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::API_CREATE])]
    #[Assert\NotBlank]
    private Event $event;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        Event::API_EXPORT,
    ])]
    #[Assert\NotBlank]
    private \DateTimeImmutable $sungAt;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
        Event::API_EXPORT,
    ])]
    #[Assert\NotBlank]
    private string $singer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getArtist(): string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    public function getSinger(): string
    {
        return $this->singer;
    }

    public function setSinger(string $singer): self
    {
        $this->singer = $singer;

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

    public function getSungAt(): \DateTimeImmutable
    {
        return $this->sungAt;
    }

    public function setSungAt(\DateTimeImmutable $sungAt): self
    {
        $this->sungAt = $sungAt;

        return $this;
    }
}
