<?php

namespace App\Enum;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    cacheHeaders: EnumApiConfig::CACHE_HEADERS,
    normalizationContext: EnumApiConfig::NORMALIZATION_CONTEXT,
)]
enum Language: string implements EnumLabelInterface
{
    use EnumApiResourcetrait;

    case AMERICAN_ENGLISH = 'en_US';
    case FRENCH = 'fr_FR';

    public static function fromAlpha2(string $alpha2): ?self
    {
        return match($alpha2) {
            'en' => self::AMERICAN_ENGLISH,
            'fr' => self::FRENCH,
            default => null,
        };
    }

    #[Groups([EnumApiConfig::GET])]
    public function getLabel(): string
    {
        return match ($this) {
            self::AMERICAN_ENGLISH => 'English (American)',
            self::FRENCH => 'Français',
        };
    }
}
