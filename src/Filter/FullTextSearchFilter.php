<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Common\Filter\SearchFilterTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Stolen from
 * https://gist.github.com/alexislefebvre/fcbbb9104c787b9ccb739ce3bb5cfe06.
 */
class FullTextSearchFilter extends AbstractFilter implements SearchFilterInterface
{
    use SearchFilterTrait;

    private const string ACCENTS_FROM = 'áàâäãåāăąæçćĉċčďđéèêëēĕėęěĝğġģĥħíìîïĩīĭįıĵķĺļľŀłńņňŋñóòôöõōŏőœŕŗřśŝşšţťŧúùûüũūŭůűųŵýÿŷźżžßÁÀÂÄÃÅĀĂĄÆÇĆĈĊČĎĐÉÈÊËĒĔĖĘĚĜĞĠĢĤĦÍÌÎÏĨĪĬĮİĴĶĹĻĽĿŁŃŅŇŊÑÓÒÔÖÕŌŎŐŒŔŖŘŚŜŞŠŢŤŦÚÙÛÜŨŪŬŮŰŲŴÝŸŶŹŻŽẞ';
    private const string ACCENTS_TO = 'aaaaaaaaaaaaccccddeeeeeeeeegggghiiiiiiiijklllllnnnnnooooooooorrrssstttuuuuuuuuuwyyyzzzssaaaaaaaaaaccccddeeeeeeeeegggghiiiiiiiijklllllnnnnnooooooooorrrssstttuuuuuuuuuwyyyzzzss';

    public const string DOCTRINE_INTEGER_TYPE = Types::INTEGER;

    private const string PROPERTY_NAME = 'search';

    protected function getIriConverter(): IriConverterInterface
    {
        return $this->iriConverter;
    }

    protected function getPropertyAccessor(): PropertyAccessorInterface
    {
        return $this->propertyAccessor;
    }

    protected function getType(string $doctrineType): string
    {
        return 'string';
    }

    /**
     * @param array<mixed> $context
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
        // This filter will work with the 'search'-query-parameter only.
        if (self::PROPERTY_NAME !== $property) {
            return;
        }

        $orExpressions = [];

        // Split the $value at spaces.
        // For each term 'or' all given properties by strategy.
        // 'And' all 'or'-parts.
        $terms = explode(' ', $value);

        foreach ($terms as $index => $term) {
            foreach ($this->properties as $property => $strategy) {
                $strategy = $strategy ?? self::STRATEGY_EXACT;
                $alias = $queryBuilder->getRootAliases()[0];
                $field = $property;

                $associations = [];
                if ($this->isPropertyNested($property, $resourceClass)) {
                    [$alias, $field, $associations] = $this->addJoinsForNestedProperty(
                        $property,
                        $alias,
                        $queryBuilder,
                        $queryNameGenerator,
                        $resourceClass,
                        Join::LEFT_JOIN,
                    );
                }

                $caseSensitive = true;
                $metadata = $this->getNestedMetadata($resourceClass, $associations);

                if ($metadata->hasField($field)) {
                    if ('id' === $field) {
                        $term = $this->getIdFromValue($term);
                    }

                    if (!$this->hasValidValues((array) $term, $this->getDoctrineFieldType($property, $resourceClass))) {
                        $this->logger->notice('Invalid filter ignored', [
                            'exception' => new \InvalidArgumentException(
                                sprintf('Values for field "%s" are not valid according to the doctrine type.', $field),
                            ),
                        ]);
                        continue;
                    }

                    // prefixing the strategy with i makes it case insensitive
                    if (str_starts_with($strategy, 'i')) {
                        $strategy = substr($strategy, 1);
                        $caseSensitive = false;
                    }

                    $orExpressions[$index][] = $this->addWhereByStrategy(
                        $strategy,
                        $queryBuilder,
                        $queryNameGenerator,
                        $alias,
                        $field,
                        $term,
                        $caseSensitive,
                    );
                }
            }
        }

        $exprBuilder = $queryBuilder->expr();
        foreach ($orExpressions as $expr) {
            $queryBuilder->andWhere($exprBuilder->orX(...$expr));
        }
    }

    protected function createWrapCase(bool $caseSensitive): \Closure
    {
        return static function (string $expr) use ($caseSensitive): string {
            if ($caseSensitive) {
                return $expr;
            }

            return sprintf('LOWER(%s)', $expr);
        };
    }

    protected function addWhereByStrategy(
        string $strategy,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $alias,
        string $field,
        mixed $value,
        bool $caseSensitive,
    ): Orx|Comparison {
        $wrapCase = $this->createWrapCase($caseSensitive);
        $valueParameter = $queryNameGenerator->generateParameterName($field);
        $exprBuilder = $queryBuilder->expr();

        $queryBuilder
            ->setParameter($valueParameter, $value)
            ->setParameter('from', self::ACCENTS_FROM)
            ->setParameter('to', self::ACCENTS_TO);

        $fieldExpr = sprintf('TRANSLATE(%s.%s, :from, :to)', $alias, $field);
        $valueExpr = sprintf('TRANSLATE(:%s, :from, :to)', $valueParameter);

        return match ($strategy) {
            self::STRATEGY_EXACT => $exprBuilder->eq(
                $wrapCase($fieldExpr),
                $wrapCase($valueExpr),
            ),
            self::STRATEGY_PARTIAL => $exprBuilder->like(
                $wrapCase($fieldExpr),
                $exprBuilder->concat("'%'", $wrapCase($valueExpr), "'%'"),
            ),
            self::STRATEGY_START => $exprBuilder->like(
                $wrapCase($fieldExpr),
                $exprBuilder->concat($wrapCase($valueExpr), "'%'"),
            ),
            self::STRATEGY_END => $exprBuilder->like(
                $wrapCase($fieldExpr),
                $exprBuilder->concat("'%'", $wrapCase($valueExpr)),
            ),
            self::STRATEGY_WORD_START => $exprBuilder->orX(
                $exprBuilder->like(
                    $wrapCase($fieldExpr),
                    $exprBuilder->concat($wrapCase($valueExpr), "'%'"),
                ),
                $exprBuilder->like(
                    $wrapCase($fieldExpr),
                    $exprBuilder->concat("'%'", $wrapCase($valueExpr)),
                ),
            ),
            default => throw new \InvalidArgumentException(sprintf('strategy %s does not exist.', $strategy)),
        };
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description['search'] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
            ];
        }

        return $description;
    }
}
