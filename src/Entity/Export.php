<?php

namespace App\Entity;

use App\Enum\ExportProgress;
use App\Enum\ExportStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Export
{
    public const string API_GET_COLLECTION = 'api:export:get_collection';
    public const string API_GET_ITEM = 'api:export:get_item';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        Event::API_GET_ITEM,
    ])]
    private int $id;

    #[ORM\OneToOne(targetEntity: Event::class, inversedBy: 'export')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
    ])]
    private Event $event;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        Event::API_GET_ITEM,
    ])]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        Event::API_GET_ITEM,
    ])]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(type: Types::STRING, enumType: ExportProgress::class)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        Event::API_GET_ITEM,
    ])]
    private ExportProgress $progress;

    #[ORM\Column(type: Types::STRING, enumType: ExportStatus::class)]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        Event::API_GET_ITEM,
    ])]
    private ExportStatus $status;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::API_GET_COLLECTION,
        self::API_GET_ITEM,
        Event::API_GET_ITEM,
    ])]
    private bool $timelapse;

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

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getProgress(): ExportProgress
    {
        return $this->progress;
    }

    public function setProgress(ExportProgress $progress): self
    {
        $this->progress = $progress;

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

    public function isTimelapse(): bool
    {
        return $this->timelapse;
    }

    public function setTimelapse(bool $timelapse): self
    {
        $this->timelapse = $timelapse;

        return $this;
    }
}
