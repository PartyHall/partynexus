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

        if (!$user instanceof User) {
            return $ctx;
        }

        if (isset($ctx[AbstractNormalizer::GROUPS])) {
            $additionalGroups = [];

            if ($this->security->isGranted('ROLE_ADMIN')) {
                foreach ($ctx[AbstractNormalizer::GROUPS] as $group) {
                    if (\str_starts_with($group, 'api:')) {
                        $additionalGroups[] = \preg_replace('/^api:/', 'api:admin:', $group, 1);
                    }
                }
            }

            $ctx[AbstractNormalizer::GROUPS] = [
                ...$ctx[AbstractNormalizer::GROUPS],
                ...$additionalGroups,
            ];
        }

        /**
         * On the GET operation for the User resource, if the user is requesting their own data,
         * we allow him to fetch more stuff.
         */
        if (
            !$extractedAttributes
            || !\array_key_exists('resource_class', $extractedAttributes)
            || !\array_key_exists('operation', $extractedAttributes)
            || User::class !== $extractedAttributes['resource_class']
            || !($extractedAttributes['operation'] instanceof Get)
            || ((string) $user->getId()) !== $request->attributes->get('id')
        ) {
            return $ctx;
        }

        if (!\array_key_exists(AbstractNormalizer::GROUPS, $ctx)) {
            $ctx[AbstractNormalizer::GROUPS] = [];
        }

        if (!\is_array($ctx[AbstractNormalizer::GROUPS])) {
            throw new \LogicException(sprintf('The "%s" context key must be an array, "%s" given.', AbstractNormalizer::GROUPS, get_debug_type($ctx[AbstractNormalizer::GROUPS])));
        }

        $ctx[AbstractNormalizer::GROUPS] = [
            ...$ctx[AbstractNormalizer::GROUPS],
            User::API_GET_ITEM_SELF,
        ];

        return $ctx;
    }
}
