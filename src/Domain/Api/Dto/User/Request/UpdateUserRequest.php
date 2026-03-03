<?php

declare(strict_types=1);

namespace App\Domain\Api\Dto\User\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $id,

        #[Assert\NotBlank]
        #[Assert\Length(max: 8)]
        public readonly string $login,

        #[Assert\NotBlank]
        #[Assert\Length(max: 8)]
        public readonly string $pass,

        #[Assert\NotBlank]
        #[Assert\Length(max: 8)]
        public readonly string $phone,
    ) {
    }
}
