<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Song;
use Doctrine\ORM\QueryBuilder;

class SongOrderExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (Song::class !== $resourceClass) {
            return;
        }

        $queryBuilder->resetDQLPart('orderBy');

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->addOrderBy("LOWER($alias.artist)", 'ASC')
            ->addOrderBy("LOWER($alias.title)", 'ASC')
        ;
    }
}
