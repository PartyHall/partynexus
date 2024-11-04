<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\SpotifyClient;

readonly class ExternalSpotifySongProvider implements ProviderInterface
{
    public function __construct(
        private SpotifyClient $client,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->client->search($uriVariables['artist'], $uriVariables['track']);
    }
}
