<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Message\PasswordUpdatedNotification;
use App\Model\PasswordSet;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<PasswordSet, Response>
 */
readonly class UserSetPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        $userId = $uriVariables['id'] ?? null;
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        if ($user->isPasswordSet()) {
            if (!$this->passwordHasher->isPasswordValid($user, $data->oldPassword)) {
                throw new BadRequestException('Old password is incorrect');
            }
        }

        $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            $data->newPassword,
        ));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new PasswordUpdatedNotification($user));

        return new Response(status: 201);
    }
}
