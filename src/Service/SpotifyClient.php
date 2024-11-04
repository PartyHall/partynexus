<?php

namespace App\Service;

use App\ApiResource\ExternalSong;
use App\Interface\ExternalSongService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyClient implements ExternalSongService
{
    private const string AUTH_URL = 'https://accounts.spotify.com/api/token';
    private const string BASE_URL = 'https://api.spotify.com/v1/search?type=track&q=';
    private const string QUERY = '%s %s';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $client,
        private readonly CacheInterface $cacheItem,
        #[Autowire(param: 'PARTYNEXUS_VERSION')]
        private readonly string $version,
        #[Autowire(param: 'PARTYNEXUS_COMMIT')]
        private readonly string $commit,
        #[Autowire(env: 'SPOTIFY_CLIENT_ID')]
        private readonly string $spotifyId,
        #[Autowire(env: 'SPOTIFY_CLIENT_SECRET')]
        private readonly string $spotifySecret,
    ) {
    }

    public function authenticate(): string
    {
        return $this->cacheItem->get('spotify_auth_token', function (ItemInterface $item): string {
            $resp = $this->client->request('POST', self::AUTH_URL, [
                'headers' => [
                    'Authorization' => 'Basic '.base64_encode($this->spotifyId.':'.$this->spotifySecret),
                ],
                'body' => ['grant_type' => 'client_credentials'],
            ]);

            $data = json_decode($resp->getContent(), true);

            $item->set($data['access_token']);
            $item->expiresAfter($data['expires_in']);

            return $data['access_token'];
        });
    }

    public function search(string $artist, string $track): array
    {
        $resp = $this->client->request(
            method: 'GET',
            url: self::BASE_URL.\rawurlencode(\sprintf(
                self::QUERY,
                $artist,
                $track,
            )),
            options: [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authenticate(),
                    'User-Agent' => \sprintf(
                        'PartyNexus/%s-%s (https://github.com/partyhall/partynexus)',
                        $this->version,
                        $this->commit
                    ),
                ],
            ],
        );

        return \array_map(
            function ($x) {
                $itm = new ExternalSong();
                $itm->id = $x['id'];
                $itm->title = $x['name'];
                $itm->artist = \join(', ', \array_map(fn ($y) => $y['name'], $x['artists']));
                $itm->cover = $x['album']['images'][0]['url'];

                return $itm;
            },
            json_decode($resp->getContent(), true)['tracks']['items'],
        );
    }
}
