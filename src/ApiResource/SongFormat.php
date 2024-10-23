<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\Operation;

enum SongFormat: string
{
    case VIDEO = 'video';
    case CDG = 'cdg';
    case TRANSPARENT_VIDEO = 'transparent_video';

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
