<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Domain\Api\Dto\User\Request\CreateUserRequest;
use App\Domain\Api\Dto\User\Request\DeleteUserRequest;
use App\Domain\Api\Dto\User\Request\UpdateUserRequest;
use App\Domain\Api\User\UserDataConverter;
use App\Domain\User\Create\UserCreator;
use App\Domain\User\Delete\UserDeleter;
use App\Domain\User\Provider\UserProvider;
use App\Domain\User\Update\UserUpdater;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/api/users')]
class UsersApiController extends AbstractController
{
    #[Route('/{id<\d+>}', name: 'v1_api_users_get', methods: ['GET'])]
    public function getUsers(
        int $id,
        Security $security,
        UserProvider $userProvider,
        UserDataConverter $userDataConverter,
    ): JsonResponse {
        $currentUser = $security->getUser();
        if (!$currentUser instanceof User) {
            throw new UnauthorizedHttpException('Authentication required.');
        }

        if ($currentUser->getId() !== $id) {
            if (!$currentUser->isRoot()) {
                throw new AccessDeniedHttpException('You do not have rights to get user.');
            }

            $requestedUser = $userProvider->provideById($id);
        } else {
            $requestedUser = $currentUser;
        }

        if (!$requestedUser) {
            throw new BadRequestException('User not found by id "' . $id . '"');
        }

        $requestedUserResponse = $userDataConverter->convert($requestedUser);

        return $this->json([
            'data' => $requestedUserResponse,
        ]);
    }

    #[Route(name: 'v1_api_users_post', methods: ['POST'])]
    public function createUser(
        #[MapRequestPayload] CreateUserRequest $createUserRequest,
        Security $security,
        UserCreator $userCreator,
        UserDataConverter $userDataConverter,
        UserProvider $userProvider,
    ): JsonResponse {
        $currentUser = $security->getUser();
        if (!$currentUser instanceof User) {
            throw new UnauthorizedHttpException('Authentication required.');
        }

        if (!$currentUser->isRoot()) {
            throw new AccessDeniedHttpException('You do not have rights to create user.');
        }

        if ($user = $userProvider->provideByLogin($createUserRequest->login)) {
            throw new BadRequestException('User with login "' . $createUserRequest->login . '" already exists');
        }

        $newUser = $userCreator->createUser(
            $createUserRequest->login,
            $createUserRequest->pass,
            $createUserRequest->phone,
            $createUserRequest->role
        );

        $newUserResponse = $userDataConverter->convert($newUser);

        return $this->json([
            'data' => [
                $newUserResponse,
            ],
        ]);
    }

    #[Route(name: 'v1_api_users_put', methods: ['PUT'])]
    public function updateUser(
        #[MapRequestPayload] UpdateUserRequest $updateUserRequest,
        Security $security,
        UserProvider $userProvider,
        UserUpdater $userUpdater,
        UserDataConverter $userDataConverter,
    ): JsonResponse {
        $currentUser = $security->getUser();
        if (!$currentUser instanceof User) {
            throw new UnauthorizedHttpException('Authentication required.');
        }

        $userForUpdate = $userProvider->provideById($updateUserRequest->id);
        if (!$userForUpdate) {
            throw new BadRequestException('User not found for id "' . $updateUserRequest->id . '"');
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
        #[MapRequestPayload] DeleteUserRequest $deleteUserRequest,
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
            throw new BadRequestException('User not found by id "' . $deleteUserRequest->id . '"');
        }

        $userDeleter->deleteUserById($user);

        return $this->json([
            'data' => [
                'deleted' => true,
            ],
        ]);
    }
}
