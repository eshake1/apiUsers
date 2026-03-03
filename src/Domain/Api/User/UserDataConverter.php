<?php

declare(strict_types=1);

namespace App\Domain\Api\User;

use App\Domain\Api\Dto\User\Response\UserResponse;
use App\Entity\User;

class UserDataConverter
{
    public function convert(User $user): UserResponse
    {
        return new UserResponse(
            $user->getId(),
            $user->getLogin(),
            $user->getPhone(),
            $user->getRole(),
        );
    }
}
