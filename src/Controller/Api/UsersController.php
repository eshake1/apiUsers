<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Domain\User\Create\UserCreator;
use App\Domain\User\Delete\UserDeleter;
use App\Domain\User\Provider\UserProvider;
use App\Domain\User\Update\UserUpdater;
use App\Dto\User\CreateUserRequest;
use App\Dto\User\DeleteUserRequest;
use App\Dto\User\UpdateUserRequest;
use App\Entity\User;
use App\Service\UserDataConverter;
use App\Service\UsersDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('/v1/api/users')]
class UsersController extends AbstractController
{
    #[Route(name: 'v1_api_users_get', methods: ['GET'])]
    public function getUsers(
        UsersDataProvider $usersDataProvider,
        Security $security,
    ): JsonResponse {
        $currentUser = $security->getUser();
        if (!$currentUser instanceof User) {
            throw new UnauthorizedHttpException('Authentication required.');
        }

        $usersData = $usersDataProvider->provideAll($currentUser);

        return $this->json(['data' => $usersData]);
    }

    #[Route(name: 'v1_api_users_post', methods: ['POST'])]
    public function createUser(
        #[MapRequestPayload] CreateUserRequest $createUserRequest,
        UserCreator $userCreator,
        UserDataConverter $userDataConverter,
        Security $security,
    ): JsonResponse {
        $currentUser = $security->getUser();
        if (!$currentUser instanceof User) {
            throw new UnauthorizedHttpException('Authentication required.');
        }

        if (!$currentUser->isRoot()) {
            throw new AccessDeniedHttpException('You do not have rights to create user.');
        }

        $newUser = $userCreator->createUser(
            $createUserRequest->login,
            $createUserRequest->pass,
            $createUserRequest->phone,
            $createUserRequest->role
        );

        $newUserData = $userDataConverter->convert($newUser);

        return $this->json([
            'data' => [
                $newUserData,
            ],
        ]);
    }

    #[Route(name: 'v1_api_users_put', methods: ['PUT'])]
    public function updateUser(
        #[MapRequestPayload] UpdateUserRequest $updateUserRequest,
        UserProvider $userProvider,
        UserUpdater $userUpdater,
        UserDataConverter $userDataConverter,
        Security $security,
    ): JsonResponse {
        $currentUser = $security->getUser();
        if (!$currentUser instanceof User) {
            throw new UnauthorizedHttpException('Authentication required.');
        }

        $userForUpdate = $userProvider->provideByLoginAndPass($updateUserRequest->login, $updateUserRequest->pass);
        if (!$userForUpdate) {
            throw new BadRequestException('User not found for login and password');
        }

        if ($currentUser->getId() !== $userForUpdate->getId() && !$currentUser->isRoot()) {
            throw new AccessDeniedHttpException('You do not have rights to update user.');
        }

        $userUpdater->updateUser(
            $userForUpdate,
            $updateUserRequest->login,
            $updateUserRequest->pass,
            $updateUserRequest->phone
        );

        $userForUpdateData = $userDataConverter->convert($userForUpdate);

        return $this->json([
            'data' => [
                $userForUpdateData,
            ],
        ]);
    }

    #[Route(name: 'v1_api_users_delete', methods: ['DELETE'])]
    public function deleteUser(
        DeleteUserRequest $deleteUserRequest,
        UserProvider $userProvider,
        UserDeleter $userDeleter,
        Security $security,
    ): JsonResponse {
        $currentUser = $security->getUser();
        if (!$currentUser instanceof User) {
            throw new UnauthorizedHttpException('Authentication required.');
        }

        if (!$currentUser->isRoot()) {
            throw new AccessDeniedHttpException('You do not have rights to delete user.');
        }

        $user = $userProvider->provideById($deleteUserRequest->id);
        if (!$user) {
            throw new BadRequestException('User not found for id');
        }

        $userDeleter->deleteUserById($user);

        return $this->json([
            'data' => [
                'deleted' => true,
            ],
        ]);
    }
}
