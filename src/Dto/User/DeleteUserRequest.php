<?php

declare(strict_types=1);

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class DeleteUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $id,
    ) {
    }
}
