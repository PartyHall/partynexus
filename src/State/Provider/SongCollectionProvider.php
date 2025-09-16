<?php

namespace App\State\Provider;

use ApiPlatform\Doctrine\Common\CollectionPaginator;
use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\ArrayPaginator;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Song;
use App\Enum\SongFormat;
use App\Service\BigSearchService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsDecorator('api_platform.doctrine.orm.state.collection_provider')]
readonly class SongCollectionProvider implements ProviderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private ProviderInterface $inner,
        private BigSearchService $searchService,
        private ManagerRegistry $managerRegistry,
        private RequestStack $requestStack,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!$operation instanceof GetCollection || $operation->getClass() !== Song::class) {
            return $this->inner->provide($operation, $uriVariables, $context);
        }

        $rq = $this->requestStack->getMainRequest();
        $query = \trim($rq->query->get('search'));

        if ($query === '') {
            return $this->inner->provide($operation, $uriVariables, $context);
        }

        $ready = \trim($rq->query->getBoolean('ready'));
        $vocals = \trim($rq->query->get('vocals'));

        $formats = $rq->query->all('format');
        if (empty($formats)) {
            $f = \trim($rq->query->get('format'));

            if ($f !== '') {
                $formats = [$f];
            }
        }

        $formats = \array_map(fn($v) => SongFormat::from(\trim($v)), $formats);

        $page = \filter_var($rq->query->get('page', 1), FILTER_VALIDATE_INT);
        $itemsPerPage = $operation->getPaginationItemsPerPage() ?? 30;

        $hits = $this->searchService->searchSong(
            $query,
            $ready === '' ? null : \filter_var($ready, FILTER_VALIDATE_BOOLEAN),
            $vocals === '' ? null : \filter_var($vocals, FILTER_VALIDATE_BOOLEAN),
            $formats,
            $page,
            $itemsPerPage
        );

        return $hits;
    }
}
