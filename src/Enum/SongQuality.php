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

    public static function getCases(): array
    {
        return self::cases();
    }

    public static function getCase(Operation $operation, array $uriVariables)
    {
        $name = $uriVariables['id'] ?? null;

        return constant(self::class . "::$name");
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
