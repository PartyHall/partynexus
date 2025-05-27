<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class HealthController
{
    /**
     * This is a simple controller that returns 200.
     * It is used by docker to ensure the app is fully started before
     * starting the workers.
     */
    #[Route('/health', name: 'health')]
    public function __invoke(): Response
    {
        return new JsonResponse(['status' => 'ok']);
    }
}
