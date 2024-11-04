<?php

namespace App\Serializer;

use App\Entity\Song;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

class SongNormalizer implements NormalizerInterface
{
    private const string ALREADY_CALLED = 'SONG_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage,
    ) {
    }

    /**
     * @param Song         $object
     * @param array<mixed> $context
     *
     * @return array<mixed>|string|int|float|bool|\ArrayObject<int, mixed>|null
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context[self::ALREADY_CALLED] = true;

        $object->coverUrl = $this->storage->resolveUri($object, 'coverFile');

        return $this->normalizer->normalize($object, $format, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Song;
    }

    /**
     * @return array<mixed>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            Song::class => true,
            '*' => false,
        ];
    }
}
