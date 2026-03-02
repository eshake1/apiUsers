<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UsersDataProvider
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserDataConverter $userDataConverter,
    ) {}

    public function provideAll(User $currentUser): array
    {
        $users = $currentUser->isRoot()
            ? $this->userRepository->findAll()
            : [$currentUser];

        $userData = [];
        foreach ($users as $user) {
            $userData[] = $this->userDataConverter->convert($user);
        }


        return $userData;
    }
}
