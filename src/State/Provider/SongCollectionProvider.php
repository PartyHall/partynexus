<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Bridge\Meilisearch\MeilisearchPaginator;
use App\Entity\Song;
use App\Enum\SongFormat;
use App\Service\BigSearchService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @implements ProviderInterface<MeilisearchPaginator<Song>>
 */
readonly class SongCollectionProvider implements ProviderInterface
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
        if (!$operation instanceof GetCollection || Song::class !== $operation->getClass()) {
            return $this->decorated->provide($operation, $uriVariables, $context);
        }

        $rq = $this->requestStack->getMainRequest();
        $query = \trim($rq->query->get('search'));

        if ('' === $query) {
            return $this->decorated->provide($operation, $uriVariables, $context);
        }

        $ready = \trim($rq->query->get('ready'));
        $vocals = \trim($rq->query->get('vocals'));

        $formats = $rq->query->all('format');
        if (empty($formats)) {
            $f = \trim($rq->query->get('format'));

            if ('' !== $f) {
                $formats = [$f];
            }
        }

        $formats = \array_map(fn ($v) => SongFormat::from(\trim($v)), $formats);

        $page = \filter_var($rq->query->get('page', 1), FILTER_VALIDATE_INT);
        $itemsPerPage = $operation->getPaginationItemsPerPage() ?? 30;

        return $this->searchService->searchSong(
            $query,
            '' === $ready ? null : \filter_var($ready, FILTER_VALIDATE_BOOLEAN),
            '' === $vocals ? null : \filter_var($vocals, FILTER_VALIDATE_BOOLEAN),
            $formats,
            $page,
            $itemsPerPage
        );
    }
}
