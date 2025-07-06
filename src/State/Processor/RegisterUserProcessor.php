<?php

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class RegisterUserProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<User, User> $processor */
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
        private EventRepository $repository,
        private EntityManagerInterface $entityManager,
        private AuthenticationSuccessHandler $authenticationSuccessHandler,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $event = $this->repository->findOneBy([
            // Fuck api platform bullshit, it tries to parse the uriVariables
            'userRegistrationCode' => $context['request']->attributes->get('userRegistrationCode') ?? null,
        ]);

        if (!$event || !$event->isUserRegistrationEnabled()) {
            throw new NotFoundHttpException();
        }

        $user = $this->processor->process($data, $operation, $uriVariables, $context);

        if (!$user instanceof User) {
            throw new \Exception('The RegisterUserProcessor has failed to persist the user');
        }

        $event->addParticipant($user);
        $this->entityManager->persist($user);
        $this->entityManager->flush();


        return $this->authenticationSuccessHandler->handleAuthenticationSuccess($user);
    }
}
