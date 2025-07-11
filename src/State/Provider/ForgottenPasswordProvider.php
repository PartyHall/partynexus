<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\ForgottenPassword;
use App\Repository\ForgottenPasswordRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<ForgottenPassword>
 */
readonly class ForgottenPasswordProvider implements ProviderInterface
{
    public function __construct(
        private ForgottenPasswordRepository $repository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $code = $uriVariables['code'];

        /** @var ?ForgottenPassword $entity */
        $entity = $this->repository->findByCode($code);
        if (!$entity) {
            throw new NotFoundHttpException('ForgottenPassword not found');
        }

        $now = new \DateTimeImmutable();

        if ($entity->isUsed() || $entity->getCreatedAt()->modify('+48 hours') < $now) {
            throw new NotFoundHttpException('ForgottenPassword not found');
        }

        return $entity;
    }
}
