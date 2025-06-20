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

    #[Groups([EnumApiConfig::GET_GROUP])]
    public function getLabel(): string
    {
        return match ($this) {
            self::AMERICAN_ENGLISH => 'English (American)',
            self::FRENCH => 'Français',
        };
    }
}
