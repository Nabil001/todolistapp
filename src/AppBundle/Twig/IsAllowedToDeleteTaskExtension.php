<?php

namespace AppBundle\Twig;

use AppBundle\Utils\TaskAuthorizationChecker;

class IsAllowedToDeleteTaskExtension extends \Twig_Extension
{
    /**
     * @var TaskAuthorizationChecker
     */
    private $taskAutorizationChecker;

    public function __construct(TaskAuthorizationChecker $taskAuthorizationChecker)
    {
        $this->taskAutorizationChecker = $taskAuthorizationChecker;
    }

    /**
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_allowed_to_delete', array($this->taskAutorizationChecker, 'isAllowedToDelete'))
        );
    }
}