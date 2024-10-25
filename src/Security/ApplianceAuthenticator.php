<?php

namespace App\Security;

use App\Repository\ApplianceRepository;
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

class ApplianceAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly ApplianceRepository $repository,
    )
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-HARDWARE-ID') && $request->headers->has('X-API-TOKEN');
    }

    public function authenticate(Request $request): Passport
    {
        $hwid = $request->headers->get('X-HARDWARE-ID');
        $token = $request->headers->get('X-API-TOKEN');

        if (empty($hwid) || empty($token)) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        $userIdentifier = $hwid;
        $user = $this->repository->findOneBy([
            'hardwareId' => $hwid,
            'apiToken' => $token,
        ]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        return new SelfValidatingPassport(new UserBadge($userIdentifier));
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
