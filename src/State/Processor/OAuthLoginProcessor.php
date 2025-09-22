<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\LoginOAuth;
use App\Repository\UserRepository;
use App\Service\OAuthClient;
use App\Service\OAuthUserManager;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<LoginOAuth, Response>
 */
readonly class OAuthLoginProcessor implements ProcessorInterface
{
    public function __construct(
        private LoggerInterface              $logger,
        private OAuthClient                  $oauthClient,
        private OAuthUserManager             $oauthUserManager,
        private UserRepository               $userRepository,
        private AuthenticationSuccessHandler $authenticationSuccessHandler,
    )
    {
    }

    /**
     * @param array<mixed> $uriVariables
     * @param array<mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        if (!$data instanceof LoginOAuth) {
            throw new \InvalidArgumentException('Data must be an instance of LoginOAuth');
        }

        try {
            $userData = $this->oauthClient->exchangeTokenAndGetUserInfos($data->code);

            $user = $this->userRepository->findOneByOauthUserId($userData->oauthUserId);
            if ($user === null) {
                $user = $this->oauthUserManager->createUser($userData);
            } else {
                $user = $this->oauthUserManager->updateUser($user, $userData);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to get user from OAuth provider: ' . $e->getMessage());

            throw new BadRequestHttpException('An error occured, contact your PartyHall administrator.');
        }

        return $this->authenticationSuccessHandler->handleAuthenticationSuccess($user);
    }
}
