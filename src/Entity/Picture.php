<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Repository\PictureRepository;
use App\State\Provider\PictureDownloadProvider;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @TODO: Get, GetCollection only for appliance that owns the event AND the users that are in the event
 * @TODO: Download => Only the users that are in the event
 */
#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/events/{eventId}/pictures',
            uriVariables: [
                'eventId' => new Link(
                    fromProperty: 'pictures',
                    fromClass: Event::class, // On veut pointer VERS LA CLASSE AUQUEL eventId FAIT REFERENCE (fromClass event d'après symfonycasts)
                )
            ],
            order: ['takenAt' => 'ASC'],
        ),
        new Get(),
        new Get(
            uriTemplate: '/pictures/{id}/download',
            provider: PictureDownloadProvider::class,
        ),
        new Post(
            uriTemplate: '/events/{eventId}/pictures',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld' => ['application/ld+json']],
            uriVariables: [
                'eventId' => new Link(
                    fromProperty: 'pictures',
                    fromClass: Event::class, // On veut pointer VERS LA CLASSE AUQUEL eventId FAIT REFERENCE (fromClass event d'après symfonycasts)
                )
            ],
            denormalizationContext: ['groups' => ['api:picture:create']]
        )
    ],
    normalizationContext: ['groups' => ['api:picture:get_item']],
)]
#[ApiFilter(BooleanFilter::class, properties: ['unattended'])]
#[ORM\Entity(repositoryClass: PictureRepository::class)]
class Picture
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
    #[Assert\NotNull]
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

    #[ORM\Column(type: UuidType::NAME)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        self::API_CREATE,
    ])]
    #[Assert\NotNull]
    #[Assert\Uuid]
    private Uuid $applianceUuid;

    #[Vich\UploadableField(mapping: 'pictures', fileNameProperty: 'filepath')]
    #[Groups([self::API_CREATE])]
    public ?File $file = null;

    #[ORM\Column(nullable: true)]
    public ?string $filepath = null;

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
     *
     * @param File|UploadedFile|null $file
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
}
