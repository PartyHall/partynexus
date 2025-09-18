<?php

namespace App\Bridge\Meilisearch;

use ApiPlatform\State\Pagination\HasNextPagePaginatorInterface;
use ApiPlatform\State\Pagination\PaginatorInterface;

// @phpstan-ignore-next-line
class MeilisearchPaginator implements PaginatorInterface, \IteratorAggregate, HasNextPagePaginatorInterface
{
    /** @var \Traversable<object>|\ArrayIterator<object> */
    private \Traversable $iterator;

    private int $count;
    private int $page;
    private int $hitsPerPage;
    private int $totalPages;
    private int $totalHits;

    /**
     * @param array<mixed> $meilisearchResponse
     */
    public function __construct(array $meilisearchResponse)
    {
        $this->count = \array_key_exists('hits', $meilisearchResponse) ? \count($meilisearchResponse['hits']) : 0;
        $this->page = $meilisearchResponse['page'] ?? 1;
        $this->hitsPerPage = $meilisearchResponse['hitsPerPage'] ?? 20;
        $this->totalPages = $meilisearchResponse['totalPages'] ?? 0;
        $this->totalHits = $meilisearchResponse['totalHits'] ?? 0;

        $this->iterator = new \ArrayIterator($meilisearchResponse['hits']);
    }

    public function count(): int
    {
        return $this->count;
    }

    public function getLastPage(): float
    {
        return (float) $this->totalPages;
    }

    public function getTotalItems(): float
    {
        return (float) $this->totalHits;
    }

    public function getCurrentPage(): float
    {
        return (float) $this->page;
    }

    public function getItemsPerPage(): float
    {
        return $this->hitsPerPage;
    }

    public function getIterator(): \Traversable
    {
        return $this->iterator;
    }

    public function hasNextPage(): bool
    {
        return $this->getCurrentPage() < $this->getLastPage();
    }
}
