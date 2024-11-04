<?php

namespace App\Enum;

use ApiPlatform\Metadata\Operation;

enum SongQuality: string
{
    case AWFUL = 'awful';
    case BAD = 'bad';
    case OK = 'ok';
    case GOOD = 'good';
    case PERFECT = 'perfect';

    /**
     * @return SongQuality[]
     */
    public static function getCases(): array
    {
        return self::cases();
    }

    /**
     * @param mixed[] $uriVariables
     */
    public static function getCase(Operation $operation, array $uriVariables): SongQuality
    {
        $name = $uriVariables['id'] ?? null;

        return constant(self::class."::$name");
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
