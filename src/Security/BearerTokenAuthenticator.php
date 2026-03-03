<?php

declare(strict_types=1);

namespace App\Security;

use App\Domain\User\Provider\UserProvider;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class BearerTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        $auth = (string) $request->headers->get('Authorization', '');
        return str_starts_with($auth, 'Bearer ');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $auth = (string) $request->headers->get('Authorization', '');
        $token = trim(substr($auth, 7));


        if ($token === '' || !str_contains($token, ':')) {
            throw new CustomUserMessageAuthenticationException('Invalid bearer token format.');
        }

        [$login, $plainPassword] = explode(':', $token, 2);
        $login = trim($login);
        $plainPassword = trim($plainPassword);

        if ($login === '' || $plainPassword === '') {
            throw new CustomUserMessageAuthenticationException('Invalid bearer token format.');
        }

        return new SelfValidatingPassport(
            new UserBadge($login, function (string $userIdentifier) use ($plainPassword): User {
                $user = $this->userProvider->provideByLogin($userIdentifier);

                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('User not found.');
                }

                if (!$this->passwordHasher->isPasswordValid($user, $plainPassword)) {
                    throw new CustomUserMessageAuthenticationException('Invalid credentials.');
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?JsonResponse
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'code' => 'AUTH_FAILED',
                'message' => $exception->getMessage(),
            ],
        ], 401);
    }
}