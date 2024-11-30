<?php

namespace App\Serializer;

use App\Entity\Backdrop;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

class BackdropNormalizer implements NormalizerInterface
{
    private const string ALREADY_CALLED = 'BACKDROP_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param array<mixed> $context
     *
     * @return array<mixed>|string|int|float|bool|\ArrayObject<int, mixed>|null
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context[self::ALREADY_CALLED] = true;

        $object->url = $this->requestStack->getMainRequest()->getUriForPath($this->storage->resolveUri($object, 'file'));

        return $this->normalizer->normalize($object, $format, $context);
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @param array<mixed> $context
     * @return bool
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Backdrop;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Backdrop::class => true,
        ];
    }
}
