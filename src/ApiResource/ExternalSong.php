<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\ExternalMusicBrainzSongProvider;
use App\State\Provider\ExternalSpotifySongProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/external/spotify/{artist}/{track}',
            provider: ExternalSpotifySongProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/external/musicbrainz/{artist}/{track}',
            provider: ExternalMusicBrainzSongProvider::class,
        ),
    ]
)]
class ExternalSong
{
    public string $id;
    public ?string $title = null;
    public ?string $artist = null;
    public ?string $cover = null;
}
