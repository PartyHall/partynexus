<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Interface\HasEvent;
use App\Repository\PictureRepository;
use App\Security\EventVoter;
use App\State\Processor\PictureProcessor;
use App\State\Provider\PictureCollectionProvider;
use App\State\Provider\PictureDownloadProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/events/{eventId}/pictures',
            uriVariables: [
                'eventId' => new Link(
                    fromProperty: 'pictures',
                    fromClass: Event::class, // On veut pointer VERS LA CLASSE AUQUEL eventId FAIT REFERENCE (fromClass event d'après symfonycasts)
                ),
            ],
            paginationEnabled: false,
            order: ['takenAt' => 'ASC'],
            provider: PictureCollectionProvider::class,
        ),
        new Get(
            security: 'is_granted("'.EventVoter::PARTICIPANT.'", object)',
        ),
        new Get(
            uriTemplate: '/pictures/{id}/download',
            provider: PictureDownloadProvider::class,
        ),
        new Post(
            // uriTemplate: '/events/{eventId}/pictures',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld' => ['application/ld+json']],
            uriVariables: [
                'eventId' => new Link(
                    fromProperty: 'pictures',
                    fromClass: Event::class, // On veut pointer VERS LA CLASSE AUQUEL eventId FAIT REFERENCE (fromClass event d'après symfonycasts)
                ),
            ],
            denormalizationContext: [AbstractNormalizer::GROUPS => ['api:picture:create']],
            processor: PictureProcessor::class,
        ),
    ],
    normalizationContext: [AbstractNormalizer::GROUPS => ['api:picture:get_item']],
)]
#[ApiFilter(BooleanFilter::class, properties: ['unattended'])]
#[ORM\Entity(repositoryClass: PictureRepository::class)]
#[QueryParameter('unattended')]
class Picture implements HasEvent
{
    public const string API_GET_COLLECTION = 'api:picture:get_collection';
    public const string API_GET_ITEM = 'api:picture:get_item';
    public const string API_CREATE = 'api:picture:create';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'pictures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
    ])]
    private Event $event;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
    ])]
    #[Assert\NotNull]
    private \DateTimeImmutable $takenAt;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
    ])]
    #[Assert\NotNull]
    private bool $unattended = false;

    /**
     * @TODO SECURITY => Don't let it in CREATE, just make an event listener to set it automatically
     * Or maybe just override it automatically in PictureProcessor idk whichever is easier
     */
    #[ORM\Column(type: UuidType::NAME)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
    ])]
    #[Assert\NotNull]
    #[Assert\Uuid]
    private Uuid $applianceUuid;

    #[Vich\UploadableField(mapping: 'pictures', fileNameProperty: 'filepath', mimeType: 'fileMimetype')]
    #[Groups([self::API_CREATE])]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $filepath = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $fileMimetype = null;

    #[Vich\UploadableField(mapping: 'pictures', fileNameProperty: 'alternateFilepath', mimeType: 'alternateFileMimetype')]
    #[Groups([self::API_CREATE])]
    private ?File $alternateFile = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $alternateFilepath = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $alternateFileMimetype = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([self::API_GET_ITEM])]
    private bool $hasAlternatePicture = false;

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getTakenAt(): \DateTimeImmutable
    {
        return $this->takenAt;
    }

    public function setTakenAt(\DateTimeImmutable $takenAt): self
    {
        $this->takenAt = $takenAt;

        return $this;
    }

    public function isUnattended(): bool
    {
        return $this->unattended;
    }

    public function setUnattended(bool $unattended): self
    {
        $this->unattended = $unattended;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setFile(File|UploadedFile|null $file = null): self
    {
        $this->file = $file;

        // We don't need this as we are only CREATING pictures
        // thus it will always have some other data to persist
        // if (null !== $imageFile) {
        // It is required that at least one field changes if you are using doctrine
        // otherwise the event listeners won't be called and the file is lost
        // $this->updatedAt = new \DateTimeImmutable();
        // }

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

    public function getApplianceUuid(): Uuid
    {
        return $this->applianceUuid;
    }

    public function setApplianceUuid(Uuid $applianceUuid): self
    {
        $this->applianceUuid = $applianceUuid;

        return $this;
    }

    public function getFileMimetype(): ?string
    {
        return $this->fileMimetype;
    }

    public function setFileMimetype(?string $fileMimetype): self
    {
        $this->fileMimetype = $fileMimetype;

        return $this;
    }

    public function getAlternateFile(): ?File
    {
        return $this->alternateFile;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setAlternateFile(File|UploadedFile|null $file = null): self
    {
        $this->alternateFile = $file;
        $this->setHasAlternatePicture(null !== $file);

        return $this;
    }

    public function getAlternateFileMimetype(): ?string
    {
        return $this->alternateFileMimetype;
    }

    public function setAlternateFileMimetype(?string $alternateFileMimetype): self
    {
        $this->alternateFileMimetype = $alternateFileMimetype;

        return $this;
    }

    public function getAlternateFilepath(): ?string
    {
        return $this->alternateFilepath;
    }

    public function setAlternateFilepath(?string $alternateFilepath): void
    {
        $this->alternateFilepath = $alternateFilepath;
    }

    public function isHasAlternatePicture(): bool
    {
        return $this->hasAlternatePicture;
    }

    public function setHasAlternatePicture(bool $hasAlternatePicture): self
    {
        $this->hasAlternatePicture = $hasAlternatePicture;

        return $this;
    }
}
