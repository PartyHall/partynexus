<?php

namespace App\Serializer;

use ApiPlatform\Serializer\AbstractItemNormalizer;
use App\Entity\Picture;
use App\Entity\Song;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * This file is awful dont look at it too much
 * it needs to be fixed at some point
 */
class FormDataDenormalizer extends AbstractItemNormalizer implements DenormalizerInterface
{
    private function stupidHack(string $key): bool
    {
        return !\in_array($key, ['title', 'artist', 'spotifyId']);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $context
     *
     * @throws ExceptionInterface
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!\is_array($data)) {
            throw new \Exception('Data should be an array');
        }

        foreach ($data as $k => $v) {
            if (\is_string($v)) {
                if (null !== filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) && $this->stupidHack($k)) {
                    $data[$k] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
                    continue;
                }

                if (false !== filter_var($v, FILTER_VALIDATE_INT) && $this->stupidHack($k)) {
                    $data[$k] = filter_var($v, FILTER_VALIDATE_INT);
                    continue;
                }

                if (false !== filter_var($v, FILTER_VALIDATE_FLOAT) && $this->stupidHack($k)) {
                    $data[$k] = filter_var($v, FILTER_VALIDATE_FLOAT);
                    continue;
                }

                // fuck but idc
                if ($v === 'null') {
                    $data[$k] = null;
                }
            }
        }

        return parent::denormalize($data, $type, $format, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Picture::class === $type || Song::class === $type;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
            'object' => null,
            Picture::class => true,
            Song::class => true,
        ];
    }
}
