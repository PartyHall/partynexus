<?php

namespace App\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\State\Pagination\ArrayPaginator;
use App\Bridge\Meilisearch\MeilisearchPaginator;
use App\Entity\Song;
use App\Enum\SongFormat;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchService;

readonly class BigSearchService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SearchService          $searchService,
        private IriConverterInterface  $iriConverter,
    )
    {
    }

    /**
     * @param array<SongFormat>|null $formats
     */
    public function searchSong(string $query, ?bool $ready = null, ?bool $vocals = null, array $formats = [], int $page = 1, int $itemsPerPage = 30): MeilisearchPaginator
    {
        $filters = [];

        if (null !== $ready) {
            $filters[] = 'ready = ' . ($ready ? 'true' : 'false');
        }

        if (null !== $vocals) {
            $filters[] = 'vocals = ' . ($vocals ? 'true' : 'false');
        }

        if (\count($formats) > 0) {
            $formatFilters = [];

            foreach ($formats as $format) {
                $formatFilters[] = 'format = "' . $this->iriConverter->getIriFromResource($format) . '"';
            }

            $filters[] = '(' . \implode(' OR ', $formatFilters) . ')';
        }

        return $this->hydrateItems($this->searchService->rawSearch(Song::class, $query, [
            'filter' => \implode(' AND ', $filters),
            'page' => $page,
            'hitsPerPage' => $itemsPerPage,
        ]), Song::class);
    }

    /**
     * Meh this is ugly but it seems that's that is the way the bundle does it too anyway
     *
     * @param array<mixed> $response
     */
    private function hydrateItems(array $response, string $class): MeilisearchPaginator
    {
        $items = [];

        foreach ($response['hits'] as $hit) {
            $item = $this->entityManager->getRepository($class)->find($hit['id']);

            if (null !== $item) {
                $items[] = $item;
            }
        }

        $response['hits'] = $items;

        return new MeilisearchPaginator($response);
    }
}
