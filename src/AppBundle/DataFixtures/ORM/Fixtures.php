<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Fixtures extends Fixture
{
    const USER_TASK_SETS = [
        [
            'title' => 'Title 1',
            'content' => 'Content 1'
        ],
        [
            'title' => 'Title 2',
            'content' => 'Content 2'
        ]
    ];

    const ADMIN_TASK_SETS = [
        [
            'title' => 'Title 3',
            'content' => 'Content 3'
        ],
        [
            'title' => 'Title 4',
            'content' => 'Content 4'
        ]
    ];

    const ANONYOUS_USER_TASK_SETS = [
        [
            'title' => 'Title 5',
            'content' => 'Content 5'
        ],
        [
            'title' => 'Title 6',
            'content' => 'Content 6'
        ]
    ];

    const USER = [
       'username' => 'user',
        'password' => 'user',
        'email' => 'user@project.dev',
        'role' => 'ROLE_USER'
    ];

    const ADMIN = [
        'username' => 'admin',
        'password' => 'admin',
        'email' => 'admin@project.dev',
        'role' => 'ROLE_ADMIN'
    ];

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $encoder = $this->container->get('security.password_encoder');

        $user = new User();
        $user->setUsername(self::USER['username']);
        $user->setEmail(self::USER['email']);
        $user->setRole(self::USER['role']);
        $user->setPassword($encoder->encodePassword($user, self::USER['password']));
        $manager->persist($user);

        $admin = new User();
        $admin->setUsername(self::ADMIN['username']);
        $admin->setEmail(self::ADMIN['email']);
        $admin->setRole(self::ADMIN['role']);
        $admin->setPassword($encoder->encodePassword($admin, self::ADMIN['password']));
        $manager->persist($admin);

        foreach (self::USER_TASK_SETS as $set) {
            $task = new Task();
            $task->setTitle($set['title']);
            $task->setContent($set['content']);
            $task->setAuthor($user);
            $manager->persist($task);
        }

        foreach (self::ADMIN_TASK_SETS as $set) {
            $task = new Task();
            $task->setTitle($set['title']);
            $task->setContent($set['content']);
            $task->setAuthor($admin);
            $manager->persist($task);
        }

        foreach (self::ANONYOUS_USER_TASK_SETS as $set) {
            $task = new Task();
            $task->setTitle($set['title']);
            $task->setContent($set['content']);
            $manager->persist($task);
        }

        $manager->flush();
    }
}