<?php

namespace App\EventListener;

use App\Exception\ProblemDetailsException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * TEMPORARY: This listener will be removed as soon as all actions are migrated to API Platform operations.
 * It normalizes ProblemDetailsException into HTTP Problem Details responses for non-API Platform endpoints.
 */
class ProblemDetailsExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof ProblemDetailsException) {
            $data = [
                '@context' => 'problem-details',
                '@type' => 'Error',
                'status' => $exception->getStatus(),
                'type' => $exception->getType(),
                'title' => $exception->getTitle(),
                'detail' => $exception->getDetail(),
                'instance' => $event->getRequest()->getUri(),
            ];
            $response = new JsonResponse(
                $data,
                $exception->getStatus(),
                ['Content-Type' => 'application/problem+json']
            );
            $event->setResponse($response);
        }
    }
}
