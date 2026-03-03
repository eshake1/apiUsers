<?php

declare(strict_types=1);

namespace App\Domain\User\Update;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserUpdater
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function updateUser(
        User $userForUpdate,
        string $login,
        string $pass,
        string $phone,
    ): void {
        if ($userForUpdate->getLogin() !== $login) {
            $userForUpdate->setLogin($login);
        }

        $passwordHash = $this->passwordHasher->hashPassword($userForUpdate, $pass);
        $userForUpdate->setPasswordHash($passwordHash);

        if ($userForUpdate->getPhone() !== $phone) {
            $userForUpdate->setPhone($phone);
        }

        try {
            $this->userRepository->save($userForUpdate);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to update user with id "' . $userForUpdate->getId() . '"', [
                'exception' => $e,
            ]);

            if ($e instanceof UniqueConstraintViolationException) {
                throw new ConflictHttpException('User with login "' . $login . '" and password already exists');
            }

            throw new BadRequestException('Error while updating user');
        }
    }
}
