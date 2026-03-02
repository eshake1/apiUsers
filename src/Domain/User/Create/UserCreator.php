<?php

declare(strict_types=1);

namespace App\Domain\User\Create;

use App\Domain\User\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserCreator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function createUser(
        string $login,
        string $pass,
        string $phone,
        UserRole $role,
    ): User {
        $user = $this->userRepository->create($login, $pass, $phone, $role);

        try {
            $this->userRepository->save($user);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to create user with login: ' . $login, [
                'exception' => $e,
            ]);

            if ($e instanceof UniqueConstraintViolationException) {
                throw new ConflictHttpException('User with login "' . $login . '" and password already exists');
            }

            throw new BadRequestException('Error while creating user');
        }

        return $user;
    }
}
