<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Enum\SongFormat;
use App\Enum\SongQuality;
use App\Filter\FullTextSearchFilter;
use App\Interface\HasTimestamps;
use App\Interface\Impl\HasTimestampsTrait;
use App\Repository\SongRepository;
use App\State\Processor\SongCompileProcessor;
use App\State\Processor\SongDecompileProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @TODO: When a video is uploaded it should be ran through ffmpeg to get it in vp9/webm
 * This means that we should have a way of telling on the frontend that this is in progress
 * thus preventing the compilation.
 */

/**
 * @see App\Doctrine\FilterSongOnReadinessExtension
 */
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_COLLECTION]]),
        new Get(normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]]),
        new Post(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_CREATE]],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Patch(
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_UPDATE]],
            security: 'is_granted("ROLE_ADMIN") and not object.ready',
        ),
        new Patch(
            uriTemplate: '/songs/{id}/compile',
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_COMPILE]],
            security: 'is_granted("ROLE_ADMIN")',
            processor: SongCompileProcessor::class,
        ),
        new Patch(
            uriTemplate: '/songs/{id}/decompile',
            normalizationContext: [AbstractNormalizer::GROUPS => [self::API_GET_ITEM]],
            denormalizationContext: [AbstractNormalizer::GROUPS => [self::API_COMPILE]],
            security: 'is_granted("ROLE_ADMIN")',
            processor: SongDecompileProcessor::class,
        ),
        new Delete(security: 'is_granted("ROLE_ADMIN")'),
    ]
)]
#[QueryParameter('ready')]
#[ApiFilter(FullTextSearchFilter::class, properties: ['title' => 'ipartial', 'artist' => 'ipartial'])]
#[ORM\Entity(repositoryClass: SongRepository::class)]
#[Vich\Uploadable]
class Song implements HasTimestamps
{
    use HasTimestampsTrait;

    public const string API_GET_ITEM = 'api:song:get';
    public const string API_GET_COLLECTION = 'api:song:get-collection';
    public const string API_CREATE = 'api:song:create';
    public const string API_UPDATE = 'api:song:create';
    public const string API_COMPILE = 'api:song:compile';
    public const string COMPILE_METADATA = 'compile:metadata';

    /** @var string[] */
    public static array $ALLOWED_FILETYPES = [
        'cover',
        'instrumental',
        'lyrics',
        'vocals',
        'full',
    ];

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
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::COMPILE_METADATA,
    ])]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::COMPILE_METADATA,
    ])]
    #[Assert\NotBlank]
    private string $artist;

    #[Vich\UploadableField(mapping: 'song_covers', fileNameProperty: 'coverName')]
    private ?File $coverFile = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $coverName = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: SongFormat::class)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::COMPILE_METADATA,
    ])]
    #[Assert\NotBlank]
    private SongFormat $format;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: SongQuality::class)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::COMPILE_METADATA,
    ])]
    #[Assert\NotBlank]
    private SongQuality $quality;

    /**
     * The unique MBID for the song, when it exists.
     */
    #[ORM\Column(type: UuidType::NAME, nullable: true)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::COMPILE_METADATA,
    ])]
    #[Assert\Uuid]
    private ?Uuid $musicBrainzId = null;

    /**
     * The id to quickly listen to it on Spotify.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
        self::COMPILE_METADATA,
    ])]
    private ?string $spotifyId = null;

    /**
     * The build id is the version of the song.
     * This let you update the song and the appliance fetch the new version.
     *
     * e.g. you have a homemade ugly version but then you buy the mp3+cdg
     * thus you can update the song file and force the appliance to
     * fetch it again
     */
    #[ORM\Column(type: UuidType::NAME, nullable: true)]
    #[Groups([
        self::API_GET_ITEM,
        self::COMPILE_METADATA,
    ])]
    #[Assert\Uuid]
    private ?Uuid $nexusBuildId;

    /**
     * The 10 seconds that are the most recognizable
     * Not sure if it will be used as PHv1 did a timelapse with this
     * and this was not great.
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups([
        self::API_CREATE,
        self::API_UPDATE,
        self::API_GET_ITEM,
        self::COMPILE_METADATA,
    ])]
    #[Assert\PositiveOrZero]
    private ?int $hotspot = null;

    /**
     * Whether the song has been compiled as a phk file
     * and is ready for the appliances to be fetched.
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    public bool $ready = false; // ??? Why can't I set it to private as I have getter & setter?

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    private bool $cover = false;

    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    public ?string $coverUrl = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    private bool $vocals = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::API_GET_ITEM,
        self::API_GET_COLLECTION,
    ])]
    private bool $combined = false;

    public function __construct()
    {
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

    public function isCover(): bool
    {
        return $this->cover;
    }

    public function setCover(bool $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    public function isVocals(): bool
    {
        return $this->vocals;
    }

    public function setVocals(bool $vocals): self
    {
        $this->vocals = $vocals;

        return $this;
    }

    public function isCombined(): bool
    {
        return $this->combined;
    }

    public function setCombined(bool $combined): self
    {
        $this->combined = $combined;

        return $this;
    }

    public function getCoverName(): ?string
    {
        return $this->coverName;
    }

    public function setCoverName(?string $coverName): void
    {
        $this->coverName = $coverName;
    }
}
