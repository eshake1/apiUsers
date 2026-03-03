<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\User\UserRole;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(columns: ['login'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const USER_ACL_ROLE = 'ROLE_USER';
    public const ROOT_ACL_ROLE = 'ROLE_ROOT';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 8)]
    private string $login;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $passwordHash;

    #[ORM\Column(type: 'string', length: 8)]
    private string $phone;

    #[ORM\Column(enumType: UserRole::class)]
    private UserRole $role;

    public function __construct(
        string $login,
        string $phone,
        UserRole $role,
    ) {
        $this->login = $login;
        $this->phone = $phone;
        $this->role = $role;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getRole(): string
    {
        return $this->role->value;
    }

    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    public function getRoles(): array
    {
        return match ($this->role) {
            UserRole::Root => [self::ROOT_ACL_ROLE, self::USER_ACL_ROLE],
            UserRole::User => [self::USER_ACL_ROLE],
        };
    }

    public function eraseCredentials(): void
    {
    }

    public function isRoot(): bool
    {
        return in_array(self::ROOT_ACL_ROLE, $this->getRoles(), true);
    }
}
