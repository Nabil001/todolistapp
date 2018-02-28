<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Task;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TaskAuthorizationChecker
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Task $task
     * @return bool
     */
    public function isAllowedToDelete(Task $task)
    {
        $author = $task->getAuthor();
        $user = $this->tokenStorage->getToken()->getUser();
        $isAdmin = $this->authorizationChecker->isGranted('ROLE_ADMIN');
        $isAuthenticated = $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY');

        return !(null === $author && !$isAdmin || null !== $author && (!$isAuthenticated || $user->getUsername() != $author->getUsername()));
    }
}