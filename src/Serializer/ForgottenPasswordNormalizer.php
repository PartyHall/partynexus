<?php

namespace App\Serializer;

use App\Entity\ForgottenPassword;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ForgottenPasswordNormalizer implements NormalizerInterface
{
    private const string ALREADY_CALLED = 'FORGOTTEN_PASSWORD_NORMALIZER_ALREADY_CALLED';
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
     * @param ForgottenPassword $object
     * @param array<mixed>      $context
     *
     * @return array<mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var array<mixed> $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['url'] = \implode(
            '/',
            [
                $this->frontUrl,
                'forgotten-password',
                $object->getCode(),
            ],
        );

        return $data;
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED]) && true === $context[self::ALREADY_CALLED]) {
            return false;
        }

        return $data instanceof ForgottenPassword;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ForgottenPassword::class => true,
        ];
    }
}
