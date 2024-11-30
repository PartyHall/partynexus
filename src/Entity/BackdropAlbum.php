<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\BackdropAlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_APPLIANCE")',
        ),
        new GetCollection(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COLLECTION]],
            security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_APPLIANCE")',
        ),
        new Post(
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Patch(
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_UPDATE]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
    normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
)]
#[ORM\Entity(repositoryClass: BackdropAlbumRepository::class)]
class BackdropAlbum
{
    public const string API_GET_COLLECTION = 'api:backdrop_album:get_collection';
    public const string API_GET_ITEM = 'api:backdrop_album:get';
    public const string API_CREATE = 'api:backdrop_album:create';
    public const string API_UPDATE = 'api:backdrop_album:update';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 128)]
    #[Assert\NotBlank]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
    ])]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 64)]
    #[Assert\NotBlank]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
    ])]
    private string $author;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
    ])]
    private int $version;

    /**
     * @var Collection<int, Backdrop>
     */
    #[ORM\OneToMany(
        targetEntity: Backdrop::class,
        mappedBy: 'album',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\OrderBy(['title' => 'ASC'])]
    #[Groups([
        self::API_GET_ITEM,
    ])]
    private Collection $backdrops;

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

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return Collection<int, Backdrop>
     */
    public function getBackdrops(): Collection
    {
        return $this->backdrops;
    }

    /**
     * @param array<Backdrop>|Collection<int, Backdrop> $backdrops
     */
    public function setBackdrops(array|Collection $backdrops): self
    {
        if (\is_array($backdrops)) {
            $backdrops = new ArrayCollection($backdrops);
        }

        foreach ($backdrops as $backdrop) {
            $backdrop->setAlbum($this);
        }

        $this->backdrops = $backdrops;

        return $this;
    }

    public function addBackdrop(Backdrop $backdrop): self
    {
        if (!$this->backdrops->contains($backdrop)) {
            $this->backdrops->add($backdrop);
            $backdrop->setAlbum($this);
        }

        return $this;
    }

    public function removeBackdrop(Backdrop $backdrop): self
    {
        $this->backdrops->removeElement($backdrop);

        return $this;
    }
}
