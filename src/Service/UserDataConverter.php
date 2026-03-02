<?php

namespace App\Service;

use App\Entity\User;

class UserDataConverter
{
    public function convert(User $user): array
    {
        return [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone(),
            'pass' => $user->getPass(),
            'role' => $user->getRole(),
        ];
    }
}