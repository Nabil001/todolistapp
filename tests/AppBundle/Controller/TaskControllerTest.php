<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\Fixtures;
use AppBundle\Entity\Task;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    const USER_USERNAME = 'user';

    const USER_PASSWORD = 'user';

    const ADMIN_USERNAME = 'admin';

    const ADMIN_PASSWORD = 'admin';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function setUp()
    {
        $this->client = self::createClient();

        $container = $this->client->getKernel()
            ->getContainer();

        $this->em = $container->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);
        $fixtures = new Fixtures();
        $fixtures->setContainer($container);
        $executor->execute([$fixtures]);
    }

    public function tearDown()
    {
        $this->em->close();
        $this->em = null;
        $this->client = null;
    }

    public function logInUser()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = self::USER_USERNAME;
        $form['_password'] = self::USER_PASSWORD;
        $this->client->submit($form);

        $this->client->followRedirect();
    }

    public function logInAdmin()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = self::ADMIN_USERNAME;
        $form['_password'] = self::ADMIN_PASSWORD;
        $this->client->submit($form);

        $this->client->followRedirect();
    }

    public function logOut()
    {
        $this->client->request('GET', '/logout');
    }

    /**
     * @param string|null $username
     * @return Task $task
     */
    public function getTask($username = null)
    {
        $builder = $this->em->createQueryBuilder();
        $builder->select('t')
            ->from(Task::class, 't');

        if (null !== $username) {
            $builder->leftJoin('t.author', 'u')
                ->addSelect('u')
                ->where('u.username = :username')
                ->setParameter('username', $username);
        } else {
            $builder->where($builder->expr()->isNull('t.author'));
        }

        $task = $builder->getQuery()
            ->getResult();

        return $task[0];
    }

    public function testTasksAnonymous()
    {
        $this->client->request('GET', '/tasks');

        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );
        $this->assertRegExp(
            '#/login$#',
            $response->headers->get('Location')
        );
    }

    public function testTasksAuthenticated()
    {
        $this->logInUser();

        $this->client->request('GET', '/tasks');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testTaskCreateAnonymous()
    {
        $this->client->request('GET', '/tasks/create');

        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );
        $this->assertRegExp(
            '#/login$#',
            $response->headers->get('Location')
        );
    }

    public function testTaskCreateAuthenticated()
    {
        $this->logInUser();

        $this->client->request('GET', '/tasks/create');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testTaskCreateValidParameters()
    {
        $this->logInUser();

        $taskRepository = $this->em->getRepository(Task::class);
        $oldTaskCount = count($taskRepository->findAll());
        $taskTitle = 'Test task title';
        $taskContent = 'Test task content';

        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = $taskTitle;
        $form['task[content]'] = $taskContent;
        $this->client->submit($form);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );

        $newTaskCount = count($this->em->getRepository(Task::class)->findAll());
        $task = $taskRepository->findOneBy(['title' => $taskTitle]);

        $this->assertEquals(
            $oldTaskCount + 1,
            $newTaskCount
        );
        $this->assertNotNull($task);
        $this->assertEquals(
            $taskContent,
            $task->getContent()
        );
        $this->assertEquals(
            self::USER_USERNAME,
            $task->getAuthor()->getUsername()
        );

        $this->logOut();
    }

    public function testTaskEditAnonymous()
    {
        $task = $this->getTask(self::USER_USERNAME);

        $this->client->request('GET', '/tasks/'.$task->getId().'/edit');

        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );
        $this->assertRegExp(
            '#/login$#',
            $response->headers->get('Location')
        );
    }

    public function testTaskEditAuthenticated()
    {
        $this->logInUser();

        $task = $this->getTask(self::USER_USERNAME);

        $this->client->request('GET', '/tasks/'.$task->getId().'/edit');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testTaskEditValidParameters()
    {
        $this->logInUser();

        $task = $this->getTask(self::USER_USERNAME);

        $old = 'old';
        $new = 'new';

        $task->setTitle($old);
        $task->setContent($old);
        $this->em->persist($task);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/tasks/'.$task->getId().'/edit');
        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = $new;
        $form['task[content]'] = $new;
        $this->client->submit($form);

        $this->em->refresh($task);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
        $this->assertEquals(
            $new,
            $task->getTitle()
        );
        $this->assertEquals(
            $new,
            $task->getContent()
        );

        $this->logOut();
    }

    public function testTaskToggleAnonymous()
    {
        $task = $this->getTask(self::USER_USERNAME);

        $this->client->request('GET', '/tasks/'.$task->getId().'/toggle');

        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );
        $this->assertRegExp(
            '#/login$#',
            $response->headers->get('Location')
        );
    }

    public function testTaskToggleAuthenticated()
    {
        $this->logInUser();

        $task = $this->getTask(self::USER_USERNAME);

        $isDone = $task->isDone();

        $this->client->request('GET', '/tasks/'.$task->getId().'/toggle');
        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );
        $this->assertRegExp(
            '#/tasks#',
            $response->headers->get('Location')
        );

        $this->em->refresh($task);
        $this->assertNotEquals(
            $isDone,
            $task->isDone()
        );

        $this->logOut();
    }

    public function testTaskDeleteAnonymous()
    {
        $task = $this->getTask(self::USER_USERNAME);

        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');

        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );
        $this->assertRegExp(
            '#/login$#',
            $response->headers->get('Location')
        );
    }

    public function testTaskDeleteUnauthorizedOnNullAuthor()
    {
        $this->logInUser();

        $task = $this->getTask();

        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');

        $this->assertEquals(
            Response::HTTP_FORBIDDEN,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testTaskDeleteAuthorizedOnNullAuthor()
    {
        $this->logInAdmin();

        $task = $this->getTask();

        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );

        $result = $this->em->createQueryBuilder()
            ->select('t')
            ->from(Task::class, 't')
            ->where('t.id = :id')
            ->setParameter('id', $task->getId())
            ->getQuery()
            ->getResult();

        $this->assertEmpty($result);

        $this->logOut();
    }

    public function testTaskDeleteUnauthorizedOnNonNullAuthor()
    {
        $this->logInUser();

        $task = $this->getTask(self::ADMIN_USERNAME);

        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');

        $this->assertEquals(
            Response::HTTP_FORBIDDEN,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testTaskDeleteAuthorizedOnNonNullAuthor()
    {
        $this->logInAdmin();

        $task = $this->getTask(self::ADMIN_USERNAME);

        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );

        $result = $this->em->createQueryBuilder()
            ->select('t')
            ->from(Task::class, 't')
            ->where('t.id = :id')
            ->setParameter('id', $task->getId())
            ->getQuery()
            ->getResult();

        $this->assertEmpty($result);

        $this->logOut();
    }
}