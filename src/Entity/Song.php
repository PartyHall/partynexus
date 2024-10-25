<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\SongFormat;
use App\ApiResource\SongQuality;
use App\Filter\FullTextSearchFilter;
use App\Interface\HasTimestamps;
use App\Interface\Impl\HasTimestampsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

// @TODO: A built song should have nothing extracted from .phk that can be exported by the admin and that is used by the
// appliance to sync songs

/**
 * @see App\Doctrine\FilterSongOnReadinessExtension
 */
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => [self::API_GET_COLLECTION]],
        ),
        new Get(
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
        ),
        // Add an operation called "add-file with parameter to choose the file-kind to add
        // Add an operation called "build" that generates the .phk, mark the song as ready to make it accessible for appliances
        new Post(
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            denormalizationContext: ['groups' => [self::API_CREATE]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Patch(
            normalizationContext: ['groups' => [self::API_GET_ITEM]],
            denormalizationContext: ['groups' => [self::API_UPDATE]],
            security: 'is_granted("ROLE_ADMIN") AND NOT object.ready',
            // @TODO: EventListener if the object goes to ready, block everything and compile the file
            // @TODO: Not sure how I want to handle this, maybe any edit unready it
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ]
)]
#[ApiFilter(BooleanFilter::class, properties: ['ready'])]
#[ApiFilter(FullTextSearchFilter::class, properties: ['title' => 'partial', 'artist' => 'partial'])]
#[ORM\Entity]
#[Vich\Uploadable]
class Song implements HasTimestamps
{
    use HasTimestampsTrait;

    public const string API_GET_ITEM = 'api:song:get';
    public const string API_GET_COLLECTION = 'api:song:get-collection';
    public const string API_CREATE = 'api:song:create';
    public const string API_UPDATE = 'api:song:create';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    private int $id;

    /**
     * The title of the song
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    #[Assert\NotBlank]
    private string $title;

    /**
     * The artist that made the song
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    #[Assert\NotBlank]
    private string $artist;

    #[Vich\UploadableField(mapping: 'song_covers', fileNameProperty: 'coverName')]
    private ?File $coverFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $coverName = null;

    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    public ?string $coverUrl = null;

    /**
     * The file format used in the phk file
     */
    #[ORM\Column(type: Types::STRING, length: 20, enumType: SongFormat::class)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    #[Assert\NotBlank]
    private SongFormat $format;

    /**
     * When a video, the quality of the video
     */
    #[ORM\Column(type: Types::STRING, length: 20, enumType: SongQuality::class)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    #[Assert\NotBlank]
    private SongQuality $quality;

    /**
     * The unique MBID for the song, when it exists
     */
    #[ORM\Column(type: UuidType::NAME, nullable: true)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
    ])]
    #[Assert\Uuid]
    private ?Uuid $musicBrainzId = null;

    /**
     * The id to quickly listen to it on Spotify
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    private ?string $spotifyId = null;

    /**
     * The build id is the version of the song.
     * This let you update the song and the appliance fetch the new version
     *
     * e.g. you have a homemade ugly version but then you buy the mp3+cdg
     * thus you can update the song file and force the appliance to
     * fetch it again
     */
    #[ORM\Column(type: UuidType::NAME, nullable: true)]
    #[Groups([
        self::API_GET_ITEM,
    ])]
    #[Assert\Uuid]
    private ?Uuid $nexusBuildId;

    /**
     * The 10 seconds that are the most recognizable
     * Not sure if it will be used as PHv1 did a timelapse with this
     * and this was not great
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
    ])]
    #[Assert\PositiveOrZero]
    private ?int $hotspot = null;

    /**
     * Whether the song has been compiled as a phk file
     * and is ready for the appliances to be fetched
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    public bool $ready = false;

    #[ORM\OneToMany(targetEntity: SongFile::class, mappedBy: 'song', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    public Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->setCreatedAt(new \DateTimeImmutable());
    }

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

    public function getCoverFile(): ?File
    {
        return $this->coverFile;
    }

    public function setCoverFile(?File $coverFile = null): self
    {
        $this->coverFile = $coverFile;

        if (null !== $coverFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getCoverName(): ?string
    {
        return $this->coverName;
    }

    public function setCoverName(?string $coverName): self
    {
        $this->coverName = $coverName;

        return $this;
    }

    public function getFormat(): SongFormat
    {
        return $this->format;
    }

    public function setFormat(SongFormat $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getQuality(): SongQuality
    {
        return $this->quality;
    }

    public function setQuality(SongQuality $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getMusicBrainzId(): ?Uuid
    {
        return $this->musicBrainzId;
    }

    public function setMusicBrainzId(?Uuid $musicBrainzId): self
    {
        $this->musicBrainzId = $musicBrainzId;

        return $this;
    }

    public function getSpotifyId(): ?string
    {
        return $this->spotifyId;
    }

    public function setSpotifyId(?string $spotifyId): self
    {
        $this->spotifyId = $spotifyId;

        return $this;
    }

    public function getNexusBuildId(): ?Uuid
    {
        return $this->nexusBuildId;
    }

    public function setNexusBuildId(?Uuid $nexusBuildId): self
    {
        $this->nexusBuildId = $nexusBuildId;

        return $this;
    }

    public function isReady(): bool
    {
        return $this->ready;
    }

    public function setReady(bool $ready): self
    {
        $this->ready = $ready;

        return $this;
    }

    public function getHotspot(): ?int
    {
        return $this->hotspot;
    }

    public function setHotspot(?int $hotspot): self
    {
        $this->hotspot = $hotspot;

        return $this;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function setFiles(Collection|array $files): void
    {
        if (is_array($files)) {
            $files = new ArrayCollection($files);
        }

        $this->files = $files;
    }
}
