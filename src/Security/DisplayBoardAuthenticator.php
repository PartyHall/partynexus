<?php

namespace App\Security;

use App\Repository\DisplayBoardKeyRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class DisplayBoardAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly DisplayBoardKeyRepository $repository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->query->has('displayBoardKey');
    }

    public function authenticate(Request $request): Passport
    {
        $displayBoardKey = $request->query->get('displayBoardKey');

        if (empty($displayBoardKey)) {
            throw new CustomUserMessageAuthenticationException('Invalid display board key');
        }

        $displayBoardKey = $this->repository->findOneBy(['key' => $displayBoardKey]);
        if (!$displayBoardKey) {
            throw new CustomUserMessageAuthenticationException('Invalid display board key');
        }

        return new SelfValidatingPassport(new UserBadge($displayBoardKey->getKey()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'message' => $exception->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
    }
}
