<?php

namespace App\Service;

use App\ApiResource\ExternalSong;
use App\Interface\ExternalSongService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusicBrainzClient implements ExternalSongService
{
    private const string BASE_URL = 'https://musicbrainz.org/ws/2/recording/?fmt=json&query=';
    private const string QUERY = 'recording:%s and artist:%s';

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly HttpClientInterface $client,
        #[Autowire(param: 'PARTYNEXUS_VERSION')]
        private readonly string $version,
        #[Autowire(param: 'PARTYNEXUS_COMMIT')]
        private readonly string $commit,
    ) {
    }

    /**
     * @var array<mixed>
     *
     * @throws InvalidArgumentException
     */
    private function fetchCover(array $song): ?string
    {
        if (0 === \count($song['releases'])) {
            return null;
        }

        $release = $song['releases'][0]['id'];

        return $this->cache->get("cover-$release", function (ItemInterface $item) use ($release): ?string {
            try {
                $resp = $this->client->request(
                    'GET',
                    'https://coverartarchive.org/release/'.$release,
                );

                $data = json_decode($resp->getContent(), true)['images'];
                $data = \array_filter($data, fn ($x) => 'Front' === $x['types'][0]);

                if (0 === \count($data)) {
                    return null;
                }

                $link = $data[0]['image'];
                $item->set($link);

                return $link;
            } catch (\Exception $e) {
                return null;
            }
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
                    'User-Agent' => \sprintf(
                        'PartyNexus/%s-%s (https://github.com/partyhall/partynexus)',
                        $this->version,
                        $this->commit
                    ),
                ],
            ],
        );

        return \array_map(
            /**
             * @param array<mixed> $x
             */
            function (array $x) {
                $itm = new ExternalSong();
                $itm->id = $x['id'];
                $itm->title = $x['title'];
                $itm->artist = \join(', ', \array_map(fn ($y) => $y['name'], $x['artist-credit']));
                // $itm->cover = $this->fetchCover($x); // This takes too long and make too many requests

                return $itm;
            },
            json_decode($resp->getContent(), true)['recordings'],
        );
    }
}
