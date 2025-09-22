<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Bridge\Meilisearch\MeilisearchPaginator;
use App\Entity\Song;
use App\Entity\SongRequest;
use App\Enum\SongFormat;
use App\Service\BigSearchService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @implements ProviderInterface<MeilisearchPaginator<SongRequest>>
 */
readonly class SongRequestCollectionProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<object> $decorated
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $decorated,
        private BigSearchService $searchService,
        private RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!$operation instanceof GetCollection || SongRequest::class !== $operation->getClass()) {
            return $this->decorated->provide($operation, $uriVariables, $context);
        }

        $rq = $this->requestStack->getMainRequest();
        $query = \trim($rq->query->get('search'));

        if ('' === $query) {
            return $this->decorated->provide($operation, $uriVariables, $context);
        }

        $page = \filter_var($rq->query->get('page', 1), FILTER_VALIDATE_INT);
        $itemsPerPage = $operation->getPaginationItemsPerPage() ?? 30;

        return $this->searchService->searchSongRequest($query, $page, $itemsPerPage);
    }
}
