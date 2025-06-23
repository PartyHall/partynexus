<?php

namespace App\Serializer;

use App\Enum\TranslatableEnumLabelInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatableEnumLabelNormalizer implements NormalizerInterface {
    private const string ALREADY_CALLED = 'TRANSLATABLE_ENUM_LABEL_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
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

        $data = $this->normalizer->normalize($object, $format, $context);

        if (!isset($data['label'])) {
            return $data;
        }

        /** @var Request $request */
        $request = $this->requestStack->getMainRequest();
        $locale = $request->getPreferredLanguage(['en_US', 'fr_FR']);

        $data['label'] = $this->translator->trans(
            id: $data['label'],
            locale: $locale,
        );

        return $data;
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof TranslatableEnumLabelInterface;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ '*' => false ];
    }
}
