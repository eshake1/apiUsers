<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/v1/api')) {
            return;
        }

        $exception = $event->getThrowable();

        $this->logger->error($exception->getMessage(), [
            'exception' => $exception,
        ]);

        $status = 500;
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
        }

        $isServerError = $status >= 500;

        $event->setResponse(new JsonResponse([
            'error' => [
                'code' => $isServerError ? 'SERVER_ERROR' : 'REQUEST_ERROR',
                'message' => $isServerError ? 'Internal server error.' : $exception->getMessage(),
            ],
        ], $status));
    }
}