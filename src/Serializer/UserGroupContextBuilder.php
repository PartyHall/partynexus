<?php

namespace App\Serializer;

use ApiPlatform\Metadata\Get;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[AsDecorator('api_platform.serializer.context_builder')]
readonly class UserGroupContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private SerializerContextBuilderInterface $decorated,
        private Security $security,
    ) {
    }

    /**
     * @param ?array<mixed> $extractedAttributes
     *
     * @return array<mixed>
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $ctx = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $user = $this->security->getUser();

        if (
            !\array_key_exists('resource_class', $extractedAttributes)
            || !\array_key_exists('operation', $extractedAttributes)
            || User::class !== $extractedAttributes['resource_class']
            || !($extractedAttributes['operation'] instanceof Get)
            || !$user instanceof User
            || ((string) $user->getId()) !== $request->attributes->get('id')
        ) {
            return $ctx;
        }

        $ctx[AbstractNormalizer::GROUPS] = [
            ...$ctx[AbstractNormalizer::GROUPS],
            User::API_GET_ITEM_SELF,
        ];

        return $ctx;
    }
}
