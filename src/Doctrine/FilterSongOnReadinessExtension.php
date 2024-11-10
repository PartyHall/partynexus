<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Song;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

// https://api-platform.com/docs/core/extensions/
final readonly class FilterSongOnReadinessExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    private function addCondition(QueryBuilder $queryBuilder, bool $val): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(sprintf('%s.ready = :ready', $rootAlias))
            ->setParameter('ready', $val);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!$operation instanceof GetCollection || Song::class !== $resourceClass) {
            return;
        }

        $isAdmin = $this->security->isGranted('ROLE_ADMIN');
        $param = 'true' === \strtolower($context['request']->query->get('ready') ?? 'true');

        if (!$isAdmin) {
            $this->addCondition($queryBuilder, true);
        } else {
            $this->addCondition($queryBuilder, $param);
        }
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!$operation instanceof Get || Song::class !== $resourceClass) {
            return;
        }

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $this->addCondition($queryBuilder, true);
        }
    }
}
