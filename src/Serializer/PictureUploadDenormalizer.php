<?php

namespace App\Serializer;

use ApiPlatform\Serializer\AbstractItemNormalizer;
use App\Entity\Picture;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PictureUploadDenormalizer extends AbstractItemNormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!\is_array($data)) {
            throw new \Exception('Data should be an array');
        }

        foreach ($data as $k => $v) {
            if (\is_string($v)) {
                if (null !== filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
                    $data[$k] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
                    continue;
                }

                if (false !== filter_var($v, FILTER_VALIDATE_INT)) {
                    $data[$k] = filter_var($v, FILTER_VALIDATE_INT);
                    continue;
                }

                if (false !== filter_var($v, FILTER_VALIDATE_FLOAT)) {
                    $data[$k] = filter_var($v, FILTER_VALIDATE_FLOAT);
                    continue;
                }
            }
        }

        return parent::denormalize($data, Picture::class, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Picture::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
            'object' => null,
            Picture::class => true,
        ];
    }
}
