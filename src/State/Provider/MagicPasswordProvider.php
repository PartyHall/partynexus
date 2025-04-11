<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\MagicPassword;
use App\Repository\MagicPasswordRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<MagicPassword>
 */
readonly class MagicPasswordProvider implements ProviderInterface
{
    public function __construct(
        private MagicPasswordRepository $magicPasswordRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $code = $uriVariables['code'];

        /** @var ?MagicPassword $entity */
        $entity = $this->magicPasswordRepository->findByCode($code);
        if (!$entity) {
            throw new NotFoundHttpException('MagicPassword not found');
        }

        $now = new \DateTimeImmutable();

        if ($entity->isUsed() || $entity->getCreatedAt()->modify('+48 hours') < $now) {
            throw new NotFoundHttpException('MagicPassword not found');
        }

        return $entity;
    }
}
