<?php

namespace App\Enum;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

enum EnumApiConfig
{
    public const string GET_GROUP = 'api:enum:get';
    public const array NORMALIZATION_CONTEXT = [AbstractNormalizer::GROUPS => self::GET_GROUP];
    public const array CACHE_HEADERS = ['max_age' => 24 * 60 * 60, 'vary' => ['Authorization']];
}
