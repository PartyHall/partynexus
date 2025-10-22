<?php

namespace App\Enum;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Operation;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    cacheHeaders: EnumApiConfig::CACHE_HEADERS,
    normalizationContext: EnumApiConfig::NORMALIZATION_CONTEXT,
)]
enum SongQuality: string implements TranslatableEnumLabelInterface
{
    use EnumApiResourcetrait;

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

    #[Groups([EnumApiConfig::GET])]
    public function getLabel(): string
    {
        return \sprintf('song.quality.%s', $this->value);
    }
}
