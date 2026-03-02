<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/v1/api')) {
            return;
        }

        $exception = $event->getThrowable();

        $status = 500;
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
        }

        $message = $status >= 500 ? 'Internal server error.' : $exception->getMessage();

        $event->setResponse(new JsonResponse([
            'error' => [
                'code' => $status >= 500 ? 'SERVER_ERROR' : 'REQUEST_ERROR',
                'message' => $message,
            ],
        ], $status));
    }
}