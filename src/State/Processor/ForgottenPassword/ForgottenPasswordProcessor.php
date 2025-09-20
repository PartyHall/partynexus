<?php

namespace App\State\Processor\ForgottenPassword;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ForgottenPassword;
use App\Message\ForgottenPasswordNotification;
use App\Model\PasswordReset;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * @implements ProcessorInterface<PasswordReset, Response>
 */
readonly class ForgottenPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository         $userRepository,
        private EntityManagerInterface $emi,
        private MessageBusInterface    $messageBus,
        private RequestStack           $requestStack,
        #[Autowire(service: 'limiter.forgotten_password')]
        private RateLimiterFactory     $forgottenPasswordApiLimiter,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        if (!$data instanceof PasswordReset) {
            throw new \InvalidArgumentException('Expected instance of ' . PasswordReset::class);
        }

        $rl = $this->forgottenPasswordApiLimiter->create($this->requestStack->getMainRequest()->getClientIp());
        if (false === $rl->consume()->isAccepted()) {
            return new Response(status: 429);
        }

        $user = $this->userRepository->findOneBy(['email' => $data->email]);
        if (!$user) {
            // Useless as people can just try to register with an email to know if it exists in DB
            // but meh, lets do it anyway
            return new Response(status: 200);
        }

        $fp = new ForgottenPassword()->setUser($user)->setCode(\bin2hex(\random_bytes(64)))->setUsed(false);
        $this->emi->persist($fp);
        $this->emi->flush();

        $this->messageBus->dispatch(new ForgottenPasswordNotification(
            $fp->getCode(),
            $user,
        ));

        return new Response(status: 200);
    }
}
