<?php

namespace App\Enum;

use ApiPlatform\Metadata\Operation;

enum SongFormat: string
{
    case VIDEO = 'video';
    case CDG = 'cdg';
    case TRANSPARENT_VIDEO = 'transparent_video';

    /**
     * @return SongFormat[]
     */
    public static function getCases(): array
    {
        return self::cases();
    }

    /**
     * @param array<mixed> $uriVariables
     */
    public static function getCase(Operation $operation, array $uriVariables): SongFormat
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
