<?php

declare(strict_types=1);

namespace App\Domain\User\Provider;

use App\Entity\User;
use App\Repository\UserRepository;

class UserProvider
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function provideById(
        int $id,
    ): ?User {
        return $this->userRepository->findById($id);
    }

    public function provideByLogin(
        string $login,
    ): ?User {
        return $this->userRepository->findByLogin($login);
    }
}
