<?php

namespace App\Entity;

use App\Enum\ExportStatus;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Export
{
    public const string API_GET_COLLECTION = 'api:export:get_collection';
    public const string API_GET_ITEM = 'api:export:get_item';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'exports')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private Event $event;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::STRING, nullable: true, enumType: ExportStatus::class)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private ?ExportStatus $status = null;

    public function getId(): int
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

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getStatus(): ExportStatus
    {
        return $this->status;
    }

    public function setStatus(ExportStatus $status): self
    {
        $this->status = $status;

        return $this;
    }
}
