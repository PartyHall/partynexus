<?php

namespace App\Doctrine\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

class SongSearchFilter extends AbstractFilter
{
    /**
     * @param mixed[] $context
     */
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ('search' !== $property || empty($value)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(\sprintf(
                'ts_query(%s.searchVector, :tsquery) = TRUE',
                $rootAlias,
            ))
            ->setParameter('tsquery', $value);
    }

    /**
     * @return mixed[]
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => 'search',
                'type' => 'string',
                'required' => false,
                'openapi' => [
                    'description' => 'Search a song by artist and title',
                ],
            ],
        ];
    }

    private function toTsQuery(string $search): string
    {
        $words = preg_split('/\s+/', trim($search));
        $words = array_filter($words, fn($w) => $w !== '');
        return implode(' & ', array_map(fn($w) => $w . ':*', $words));
    }
}
