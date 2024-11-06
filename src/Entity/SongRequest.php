<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Filter\FullTextSearchFilter;
use App\Interface\HasTimestamps;
use App\Interface\Impl\HasTimestampsTrait;
use App\State\Processor\SongRequestProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM, HasTimestamps::API_GET, User::API_GET_ITEM]]),
        new GetCollection(normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM, HasTimestamps::API_GET, User::API_GET_ITEM]]),
        new Post(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM, HasTimestamps::API_GET, User::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            processor: SongRequestProcessor::class,
        ),
        new Delete(security: 'is_granted("ROLE_ADMIN")'),
    ],
)]
#[ApiFilter(FullTextSearchFilter::class, properties: ['title', 'artist', 'requestedBy'])]
class SongRequest implements HasTimestamps
{
    use HasTimestampsTrait;

    public const string API_GET_ITEM = 'api:song_request:get-item';
    public const string API_GET_COLLECTION = 'api:song_request:get-collection';
    public const string API_CREATE = 'api:song_request:create';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
    ])]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::API_CREATE,
    ])]
    private string $artist;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'songRequests')]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    private User $requestedBy;

    public function getId(): int
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

    public function getRequestedBy(): User
    {
        return $this->requestedBy;
    }

    public function setRequestedBy(User $requestedBy): self
    {
        $this->requestedBy = $requestedBy;

        return $this;
    }
}
