<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    uriTemplate: '/backdrop_albums/{albumId}/backdrops',
    operations: [
        new Get(
            uriTemplate: '/backdrop_albums/{albumId}/backdrops/{id}',
            uriVariables: [
                'albumId' => new Link(
                    fromProperty: 'backdrops',
                    fromClass: BackdropAlbum::class, // On veut pointer VERS LA CLASSE AUQUEL eventId FAIT REFERENCE (fromClass event d'après symfonycasts)
                ),
                'id' => new Link(fromClass: self::class),
            ],
            security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_APPLIANCE")',
        ),
        new GetCollection(
            uriTemplate: '/backdrop_albums/{albumId}/backdrops',
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COLLECTION]],
            security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_APPLIANCE")'
        ),
        new Post(
            uriTemplate: '/backdrops',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld' => ['application/ld+json']],
            uriVariables: [],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Patch(
            uriTemplate: '/backdrop_albums/{albumId}/backdrops/{id}',
            uriVariables: [
                'albumId' => new Link(
                    fromProperty: 'backdrops',
                    fromClass: BackdropAlbum::class, // On veut pointer VERS LA CLASSE AUQUEL eventId FAIT REFERENCE (fromClass event d'après symfonycasts)
                ),
                'id' => new Link(fromClass: self::class),
            ],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_UPDATE]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Delete(
            uriTemplate: '/backdrop_albums/{albumId}/backdrops/{id}',
            uriVariables: [
                'albumId' => new Link(
                    fromProperty: 'backdrops',
                    fromClass: BackdropAlbum::class, // On veut pointer VERS LA CLASSE AUQUEL eventId FAIT REFERENCE (fromClass event d'après symfonycasts)
                ),
                'id' => new Link(fromClass: self::class),
            ],
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
    uriVariables: [
        'albumId' => new Link(
            fromProperty: 'backdrops',
            fromClass: BackdropAlbum::class, // On veut pointer VERS LA CLASSE AUQUEL eventId FAIT REFERENCE (fromClass event d'après symfonycasts)
        ),
    ],
    normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
)]
#[ORM\Entity]
#[Vich\Uploadable]
class Backdrop
{
    public const string API_GET_COLLECTION = 'api:backdrops:get_collection';
    public const string API_GET_ITEM = 'api:backdrops:get';
    public const string API_CREATE = 'api:backdrops:create';
    public const string API_UPDATE = 'api:backdrops:update';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        BackdropAlbum::API_GET_ITEM,
    ])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 128)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
        self::API_UPDATE,
        BackdropAlbum::API_GET_ITEM,
        BackdropAlbum::EXPORT,
    ])]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\ManyToOne(targetEntity: BackdropAlbum::class, inversedBy: 'backdrops')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_CREATE,
    ])]
    private BackdropAlbum $album;

    #[Vich\UploadableField(
        mapping: 'backdrops',
        fileNameProperty: 'filepath',
    )]
    #[Groups([self::API_CREATE])]
    #[Assert\NotNull(groups: [self::API_CREATE])]
    #[Assert\Image(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        detectCorrupted: true,
    )]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups([
        BackdropAlbum::EXPORT,
    ])]
    private ?string $filepath = null;

    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        BackdropAlbum::API_GET_ITEM,
    ])]
    public ?string $url = null;

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

    public function getAlbum(): BackdropAlbum
    {
        return $this->album;
    }

    public function setAlbum(BackdropAlbum $album): self
    {
        $this->album = $album;
        $album->addBackdrop($this);

        return $this;
    }

    public function getFilepath(): ?string
    {
        return $this->filepath;
    }

    public function setFilepath(?string $filepath): self
    {
        $this->filepath = $filepath;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }
}
