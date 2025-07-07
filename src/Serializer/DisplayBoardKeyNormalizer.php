<?php

namespace App\Serializer;

use App\Entity\BackdropAlbum;
use App\Entity\DisplayBoardKey;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DisplayBoardKeyNormalizer implements NormalizerInterface
{
    private const string ALREADY_CALLED = 'DBK_NORMALIZER_ALREADY_CALLED';
    private string $frontUrl;

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        #[Autowire(env: 'PUBLIC_URL')]
        string $frontUrl,
    ) {
        $this->frontUrl = \rtrim($frontUrl, '/');
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

        /** @var array<mixed> $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['url'] = \implode('/', [
            $this->frontUrl,
            'display-board',
            $object->getEvent()->getId(),
            $object->getKey(),
        ]);

        return $data;
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (
            isset($context[self::ALREADY_CALLED])
            || (
                \array_key_exists(AbstractNormalizer::GROUPS, $context)
                && \in_array(BackdropAlbum::EXPORT, $context[AbstractNormalizer::GROUPS])
            )
        ) {
            return false;
        }

        return $data instanceof DisplayBoardKey;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DisplayBoardKey::class => true,
        ];
    }
}
