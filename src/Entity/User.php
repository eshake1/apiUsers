<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\User\UserRole;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(columns: ['login', 'pass'])]
class User implements UserInterface
{
    public const USER_ACL_ROLE = 'ROLE_USER';
    public const ROOT_ACL_ROLE = 'ROLE_ROOT';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 8)]
    private string $login;

    #[ORM\Column(type: 'string', length: 8)]
    private string $pass;

    #[ORM\Column(type: 'string', length: 8)]
    private string $phone;

    #[ORM\Column(
        enumType: UserRole::class,
        options: ['default' => UserRole::User],
    )]
    private UserRole $role = UserRole::User;

    public function __construct(
        string $login,
        string $pass,
        string $phone,
        UserRole $role,
    ) {
        $this->login = $login;
        $this->pass = $pass;
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

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getPass(): string
    {
        return $this->pass;
    }

    public function setPass(string $pass): self
    {
        $this->pass = $pass;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role->value;
    }

    public function setRole(UserRole $role): void
    {
        $this->role = $role;
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
