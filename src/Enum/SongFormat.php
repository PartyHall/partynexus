<?php

namespace App\Enum;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Operation;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    cacheHeaders: EnumApiConfig::CACHE_HEADERS,
    normalizationContext: EnumApiConfig::NORMALIZATION_CONTEXT,
)]
enum SongFormat: string implements TranslatableEnumLabelInterface
{
    use EnumApiResourcetrait;

    /** Note: Values should ALWAYS BE lowercase */
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

    #[Groups([EnumApiConfig::GET_GROUP])]
    public function getLabel(): string
    {
        return match ($this) {
            self::VIDEO => 'song.format.video',
            self::CDG => 'song.format.cdg',
            self::TRANSPARENT_VIDEO => 'song.format.transparent_video',
        };
    }
}
