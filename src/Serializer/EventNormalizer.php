<?php

namespace App\Serializer;

use App\Entity\Event;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventNormalizer implements NormalizerInterface
{
    private const string ALREADY_CALLED = 'EVENT_NORMALIZER_ALREADY_CALLED';

    private string $baseUrl;

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        #[Autowire(env: 'PUBLIC_URL')]
        string $baseUrl,
    ) {
        $this->baseUrl = \rtrim($baseUrl, '/');
    }

    /**
     * @param Event        $object
     * @param array<mixed> $context
     *
     * @return array<mixed>|string|int|float|bool|\ArrayObject<int, mixed>|null
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array<mixed> $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        if ($object->getUserRegistrationCode()) {
            $data['userRegistrationUrl'] = \implode(
                '/',
                [
                    $this->baseUrl,
                    'register',
                    $object->getUserRegistrationCode(),
                ]
            );
        } else {
            $data['userRegsistrationUrl'] = null;
        }

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

        return $data instanceof Event;
    }

    /**
     * @return array<mixed>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            Event::class => true,
            '*' => false,
        ];
    }
}
