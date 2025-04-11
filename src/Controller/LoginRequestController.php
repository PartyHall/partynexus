<?php

namespace App\Controller;

use App\Entity\MagicLink;
use App\Entity\User;
use App\Message\MagicLinkNotification;
use App\Repository\MagicLinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

class LoginRequestController extends AbstractController
{
    #[Route('/api/magic-login', name: 'magic-login')]
    public function requestLoginLink(
        #[Autowire(service: 'limiter.magic_link')]
        RateLimiterFactory $magicLinkApiLimiter,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {
        if (!$request->isMethod('POST')) {
            return new Response(status: 405);
        }

        $email = $request->getPayload()->get('email');
        if (!$email) {
            return new Response(status: 400);
        }

        $limiter = $magicLinkApiLimiter->create($email);
        if (false === $limiter->consume()->isAccepted()) {
            return new Response(status: 429);
        }

        /** @var ?User $user */
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            // We should not let the user known whether the email exists or not
            return new Response(status: 200);
        }

        $link = (new MagicLink())
            ->setCode(\bin2hex(\random_bytes(64)))
            ->setUsed(false);

        $user->addMagicLink($link);
        $entityManager->persist($user);
        $entityManager->flush();

        $messageBus->dispatch(new MagicLinkNotification(
            $user->getLanguage(),
            $user->getUsername(),
            $user->getFirstname(),
            $user->getLastname(),
            $user->getEmail(),
            $link->getCode(),
        ));

        return new Response(status: 200);
    }

    #[Route('/api/magic-login-callback')]
    public function doAuthenticate(
        Request $request,
        EntityManagerInterface $entityManager,
        MagicLinkRepository $magicLinkRepo,
        AuthenticationSuccessHandler $authenticationSuccessHandler,
        #[Autowire(env: 'MAGIC_LINK_EXPIRATION')]
        string $linkExpiry,
    ): Response {
        $payload = $request->getPayload();

        if (!$payload->has('email') || !$payload->has('code')) {
            return new Response(status: 400);
        }

        $email = $payload->get('email');
        $code = $payload->get('code');

        $magicLink = $magicLinkRepo->findByEmailAndCode($email, $code);
        if (!$magicLink) {
            return new Response(status: 404);
        }

        if ($magicLink->isUsed()) {
            return new Response(status: 409);
        }

        $now = new \DateTimeImmutable();
        if ($magicLink->getCreatedAt()->modify('+'.$linkExpiry) < $now) {
            return new Response(status: 410);
        }

        $response = $authenticationSuccessHandler->handleAuthenticationSuccess(
            $magicLink->getUser(),
        );

        $magicLink->setUsed(true);
        $entityManager->persist($magicLink);
        $entityManager->flush();

        return $response;
    }
}
