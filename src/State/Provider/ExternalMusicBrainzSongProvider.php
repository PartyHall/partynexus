<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\MusicBrainzClient;

/**
 * @implements ProviderInterface<\App\ApiResource\ExternalSong>
 */
readonly class ExternalMusicBrainzSongProvider implements ProviderInterface
{
    public function __construct(
        private MusicBrainzClient $client,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->client->search($uriVariables['artist'], $uriVariables['track']);
    }
}
