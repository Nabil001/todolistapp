<?php

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserRefresher
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param User $user
     * @return void
     */
    public function refreshIfAuthenticated(User $user)
    {
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();

        if (is_a($authenticatedUser, User::class) && $authenticatedUser->getUsername() == $user->getUsername()) {
            $this->tokenStorage->setToken(
                new UsernamePasswordToken(
                    $user,
                    null,
                    'main',
                    $user->getRoles()
                )
            );
        }
    }
}