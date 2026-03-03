<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\User\UserRole;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository
{
   private EntityRepository $entityRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function findById(int $id): ?User
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findAll(): array
    {
        return $this->getEntityRepository()->findAll();
    }

    public function findByLogin(string $login): ?User
    {
        return $this->getEntityRepository()->findOneBy(['login' => $login]);
    }

    public function create(
        string $login,
        string $phone,
        UserRole $role,
    ): User {
        return new User(
            $login,
            $phone,
            $role
        );
    }

    public function save(
        User $user
    ): void {
        if (!$user->getId()) {
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
    }

    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    private function getEntityRepository(): EntityRepository
    {
        return $this->entityRepository ??= $this->entityManager->getRepository(User::class);
    }
}
