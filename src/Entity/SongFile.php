<?php

namespace App\Entity;

use App\Enum\SongFileType;
use App\Interface\HasTimestamps;
use App\Interface\Impl\HasTimestampsTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class SongFile implements HasTimestamps
{
    use HasTimestampsTrait;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Song::class, inversedBy: "files")]
    #[ORM\JoinColumn(nullable: false)]
    private Song $song;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 64, enumType: SongFileType::class)]
    private ?SongFileType $type;

    #[Vich\UploadableField(mapping: 'songs_extracted', fileNameProperty: 'filename')]
    private ?File $file = null;

    #[ORM\Column(nullable: true)]
    private ?string $filename = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getSong(): Song
    {
        return $this->song;
    }

    public function setSong(Song $song): self
    {
        $this->song = $song;

        return $this;
    }

    public function getType(): ?SongFileType
    {
        return $this->type;
    }

    public function setType(?SongFileType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null): self
    {
        $this->file = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }
}
