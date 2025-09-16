<?php

namespace App\Bridge\Meilisearch;

use ApiPlatform\State\Pagination\HasNextPagePaginatorInterface;
use ApiPlatform\State\Pagination\PaginatorInterface;
use Traversable;

class MeilisearchPaginator implements PaginatorInterface, \IteratorAggregate, HasNextPagePaginatorInterface
{
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
        $this->count = \count($meilisearchResponse['hits']) ?? 0;
        $this->page = $meilisearchResponse['page'] ?? 1;
        $this->hitsPerPage = $meilisearchResponse['hitsPerPage'] ?? 20;
        $this->totalPages = $meilisearchResponse['totalPages'] ?? 0;
        $this->totalHits = $meilisearchResponse['totalHits'] ?? 0;

        if ($this->count > 0 && 1 < $this->totalHits) {
            $this->iterator = new \LimitIterator(new \ArrayIterator($meilisearchResponse['hits']), 1, $this->hitsPerPage);
        } else {
            $this->iterator = new \EmptyIterator();
        }
    }

    public function count(): int
    {
        return $this->count;
    }

    public function getLastPage(): float
    {
        return (float)$this->totalPages;
    }

    public function getTotalItems(): float
    {
        return (float)$this->totalHits;
    }

    public function getCurrentPage(): float
    {
        return (float)$this->page;
    }

    public function getItemsPerPage(): float
    {
        return $this->hitsPerPage;
    }

    public function getIterator(): Traversable
    {
        return $this->iterator;
    }

    public function hasNextPage(): bool
    {
        return $this->getCurrentPage() < $this->getLastPage();
    }
}
