<?php

declare(strict_types=1);

namespace App\Domain\Api\Dto\User\Response;

class UserResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $login,
        public readonly string $phone,
        public readonly string $role,
    ) {
    }
}
