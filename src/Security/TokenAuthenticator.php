<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $apiTokenRepo;

    public function __construct(ApiTokenRepository $apiTokenRepo)
    {
        $this->apiTokenRepo = $apiTokenRepo;
    }

    public function supports(Request $request)
    {
        switch ($request->attributes->get('_route')) {
            case 'saveUser':
                return $request->isMethod('POST');
            case 'updateUser':
                return $request->isMethod('PUT');
            case 'getAllUsers':
                return $request->isMethod('GET');
            case 'getOneUser':
                return $request->isMethod('GET');
            case 'deleteOneUser':
                return $request->isMethod('DELETE');
        }
        return false;
    }

    public function getCredentials(Request $request)
    {
        return $request->headers->get('AUTH-TOKEN');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = $this->apiTokenRepo->findOneBy(['token' => $credentials]);

        if (!$token) {
            throw new CustomUserMessageAuthenticationException(
                'Invalid API Token'
            );
        }

        if ($token->isExpired()) {
            throw new CustomUserMessageAuthenticationException(
                'Token expired'
            );
        }
        return $token->getUser();
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(
            ['message' => $exception->getMessageKey()], 
            JsonResponse::HTTP_UNAUTHORIZED,
            ['Access-Control-Allow-Origin' => 'https://editor.swagger.io']
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // allow the authentication to continue
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // todo
    }

    public function supportsRememberMe()
    {
        // todo
    }
}
