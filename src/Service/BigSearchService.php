<?php

namespace App\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Song;
use App\Enum\SongFormat;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchService;

readonly class BigSearchService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SearchService          $searchService,
        private IriConverterInterface $iriConverter,
    )
    {
    }

    /**
     * @param array<SongFormat>|null $formats
     * @return array<Song>
     */
    public function searchSong(string $query, ?bool $ready = null, ?bool $vocals = null, array $formats = []): array
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

        return $this->searchService->search(
            $this->entityManager,
            Song::class,
            $query,
            ['filter' => \implode(' AND ', $filters)],
        );
    }
}
