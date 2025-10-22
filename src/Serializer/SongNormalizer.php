<?php

namespace App\Serializer;

use App\Entity\Song;
use App\Enum\SongFormat;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

class SongNormalizer implements NormalizerInterface
{
    private const string ALREADY_CALLED = 'SONG_NORMALIZER_ALREADY_CALLED';

    private string $baseUrl;

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        #[Autowire(env: 'PUBLIC_URL')]
        string $baseUrl,
        #[Autowire(env: 'SONG_EXTRACT_LOCATION')]
        private readonly string $wipLocation,
        private readonly StorageInterface $storage,
    ) {
        $this->baseUrl = \rtrim($baseUrl, '/');
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

        // @TODO: Remove when I'll be using a custom Api Resource
        if (!$object->getNexusBuildId()) {
            $ext = SongFormat::CDG === $object->getFormat() ? 'mp3' : 'webm';

            if (\file_exists(Path::join($this->wipLocation, \sprintf('%s', $object->getId()), "instrumental.$ext"))) {
                $object->instrumentalUrl = $this->baseUrl.'/api/song_file/'.$object->getId().'/instrumental.'.$ext;
            }

            if (\file_exists(Path::join($this->wipLocation, \sprintf('%s', $object->getId()), 'vocals.mp3'))) {
                $object->vocalsUrl = $this->baseUrl.'/api/song_file/'.$object->getId().'/vocals.mp3';
            }

            if (\file_exists(Path::join($this->wipLocation, \sprintf('%s', $object->getId()), 'full.mp3'))) {
                $object->combinedUrl = $this->baseUrl.'/api/song_file/'.$object->getId().'/full.mp3';
            }

            if (SongFormat::CDG === $object->getFormat()) {
                $object->cdgFileUploaded = \file_exists(Path::join($this->wipLocation, \sprintf('%s', $object->getId()), 'lyrics.cdg'));
            }
        }

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
