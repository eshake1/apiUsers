<?php

declare(strict_types=1);

namespace App\Dto\User;

use App\Domain\User\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 8)]
        public readonly string $login,

        #[Assert\NotBlank]
        #[Assert\Length(max: 8)]
        public readonly string $pass,

        #[Assert\NotBlank]
        #[Assert\Length(max: 8)]
        public readonly string $phone,

        public readonly UserRole $role,
    ) {
    }
}
