<?php

namespace App\Serializer;

use ApiPlatform\Hydra\Serializer\PartialCollectionViewNormalizer;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\PartialPaginatorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Thanks API Platform to be AS USUAL simultaneously such an AMAZING framework to work with, and a pain in the ass.
 */
#[AsDecorator(decorates: 'api_platform.hydra.normalizer.partial_collection_view')]
readonly class SanePartialCollectionViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    public function __construct(
        #[AutowireDecorated]
        private PartialCollectionViewNormalizer $inner,
    ) {
    }

    // IDC about HydraPrefix we'll assume there is none
    // as it will probably be used only for this software
    /**
     * @param array<mixed> $context
     *
     * @return array<mixed>|string|int|float|bool|\ArrayObject<int, mixed>|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->inner->normalize($object, $format, $context);

        if (!\array_key_exists('view', $data)) {
            return $data;
        }

        $data['view']['first'] = 1;

        if ($object instanceof PartialPaginatorInterface) {
            $data['view']['itemsPerPage'] = $object->getItemsPerPage();
            $data['view']['current'] = $object->getCurrentPage();

            if (1. !== $object->getCurrentPage()) {
                $data['view']['previous'] = $object->getCurrentPage() - 1;
            }

            if ($object instanceof PaginatorInterface) {
                if ($object->getLastPage() !== $object->getCurrentPage()) {
                    $data['view']['next'] = $object->getCurrentPage() + 1;
                }

                $data['view']['last'] = $object->getLastPage();
            }
        }

        return $data;
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->inner->supportsNormalization($data, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->inner->getSupportedTypes($format);
    }

    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->inner->setNormalizer($normalizer);
    }
}
