<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Model\PasswordSet;
use App\Repository\MagicPasswordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<PasswordSet, Response>
 */
readonly class MagicPasswordSetProcessor implements ProcessorInterface
{
    public function __construct(
        private MagicPasswordRepository $magicPasswordRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        $code = $uriVariables['code'] ?? null;

        $entity = $this->magicPasswordRepository->findByCode($code);
        if (!$entity) {
            throw new NotFoundHttpException('MagicPassword not found, expired or already used');
        }

        $now = new \DateTimeImmutable();

        if ($entity->isUsed() || $entity->getCreatedAt()->modify('+48 hours') < $now) {
            throw new NotFoundHttpException('MagicPassword not found, expired or already used');
        }

        // No need to check the old password
        // as it's kind of a reset password

        $entity->setUsed(true);

        $entity->getUser()->setPassword($this->passwordHasher->hashPassword(
            $entity->getUser(),
            $data->newPassword,
        ));

        $this->entityManager->persist($entity);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return new Response(status: 201);
    }
}
