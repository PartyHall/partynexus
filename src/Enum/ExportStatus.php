<?php

namespace App\Enum;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    cacheHeaders: EnumApiConfig::CACHE_HEADERS,
    normalizationContext: EnumApiConfig::NORMALIZATION_CONTEXT,
)]
enum ExportStatus: string implements TranslatableEnumLabelInterface
{
    use EnumApiResourcetrait;

    case STARTED = 'started';
    case COMPLETE = 'complete';
    case FAILED = 'failed';

    #[Groups([EnumApiConfig::GET])]
    public function getLabel(): string
    {
        return \sprintf('events.export.status.%s', $this->value);
    }
}
