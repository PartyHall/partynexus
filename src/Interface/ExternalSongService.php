<?php

namespace App\Interface;

use App\ApiResource\ExternalSong;

interface ExternalSongService
{
    /**
     * @return ExternalSong[]
     */
    public function search(string $artist, string $track): array;
}
