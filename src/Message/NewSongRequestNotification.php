<?php

namespace App\Message;

readonly class NewSongRequestNotification
{
    public function __construct(
        private string $title,
        private string $artist,
        private string $requestedBy,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getArtist(): string
    {
        return $this->artist;
    }

    public function getRequestedBy(): string
    {
        return $this->requestedBy;
    }
}
