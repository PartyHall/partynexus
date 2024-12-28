<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Stolen from https://github.com/opsway/doctrine-dbal-postgresql/blob/master/src/Doctrine/DBAL/Types/TsVector.php
 * As I'm not sure the library is maintained.
 */
class TsVectorType extends Type
{
    public const string TYPE = 'tsvector';

    public function getName(): string
    {
        return self::TYPE;
    }

    public function canRequireSQLConversion(): bool
    {
        return true;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return self::TYPE;
    }

    /**
     * @param string|null $value
     *
     * @return mixed[]
     * @psalm-suppress all
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        $terms = [];
        if (!empty($value)) {
            foreach (\explode(' ', $value) as $item) {
                [$term] = \explode(':', $item);
                $terms[] = \trim($term, '\'');
            }
        }

        return $terms;
    }

    /**
     * @param string $sqlExpr
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        return \sprintf('to_tsvector(%s)', $sqlExpr);
    }

    /**
     * @param mixed[]|string $value
     *
     * @psalm-suppress all
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        if (!$value) {
            return '';
        }

        if (\is_array($value)) {
            $value = \implode(' ', $value);
        }

        return $value;
    }
}
