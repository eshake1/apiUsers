<?php

declare(strict_types=1);

namespace App\Domain\User\Delete;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UserDeleter
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function deleteUser(
        User $user,
    ): void {
        $userId = $user->getId();

        try {
            $this->userRepository->deleteUser($user);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to delete user with id "' . $userId . '"', [
                'exception' => $e,
            ]);

            throw new BadRequestException('Error while deleting user');
        }
    }
}
