<?php

namespace App\Enum;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    cacheHeaders: EnumApiConfig::CACHE_HEADERS,
    normalizationContext: EnumApiConfig::NORMALIZATION_CONTEXT,
)]
enum ExportProgress: string implements TranslatableEnumLabelInterface
{
    use EnumApiResourcetrait;

    case STARTED = 'started';
    case ADDING_PICTURES = 'adding_pictures';
    case GENERATING_TIMELAPSE = 'generating_timelapse';
    case ADDING_METADATA = 'adding_metadata';
    case BUILDING_ZIP = 'building_zip';

    #[Groups([EnumApiConfig::GET])]
    public function getLabel(): string
    {
        return \sprintf('events.export.progress.%s', $this->value);
    }
}
