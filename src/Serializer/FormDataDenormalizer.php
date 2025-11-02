<?php

namespace App\Serializer;

use ApiPlatform\Serializer\AbstractItemNormalizer;
use App\Entity\Picture;
use App\Entity\Song;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * This file is awful dont look at it too much
 * it needs to be fixed at some point.
 */
class FormDataDenormalizer extends AbstractItemNormalizer implements DenormalizerInterface
{
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

        $reflection = new \ReflectionClass($type);

        foreach ($data as $k => $v) {
            if ('id' === $k || !$reflection->hasProperty($k)) {
                continue;
            }

            $property = $reflection->getProperty($k);
            $propertyType = $property->getType();

            if (null === $propertyType) {
                continue;
            }

            // @phpstan-ignore-next-line
            $typeName = $propertyType->getName();

            if ('null' === $v) {
                $data[$k] = null;

                continue;
            }

            if (!\is_string($v)) {
                continue;
            }

            $data[$k] = match ($typeName) {
                'int' => \filter_var($v, FILTER_VALIDATE_INT),
                'float' => \filter_var($v, FILTER_VALIDATE_FLOAT),
                'bool' => \filter_var($v, FILTER_VALIDATE_BOOLEAN),
                default => $v,
            };
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

    /**
     * @param array<mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return false;
    }

    /**
     * @return array<string, bool|null>
     */
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
