<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\Fixtures;
use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
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
     * @return User
     */
    public function getUser()
    {
        return $this->em->getRepository(User::class)->findAll()[0];
    }

    public function testUsersAnonymous()
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

    public function testUsersUnauthorized()
    {
        $this->logInUser();

        $this->client->request('GET', '/users');

        $this->assertEquals(
            Response::HTTP_FORBIDDEN,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testUsersAuthorized()
    {
        $this->logInAdmin();

        $this->client->request('GET', '/users');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testUserCreate()
    {
        $this->client->request('GET', '/users/create');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testUserCreateInvalidParameters()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $username = 'newUsername';
        $password = 'newPassword!5548';
        $form['user[username]'] = $username;
        $form['user[password][first]'] = $password;
        $form['user[password][second]'] = $password;
        $form['user[email]'] = 'u';
        $form['user[role]'] = 'ROLE_USER';
        $this->client->submit($form);

        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
    }

    public function testUserCreateValidParameters()
    {
        $crawler = $this->client->request('GET', '/users/create');

        $userRepository = $this->em->getRepository(User::class);
        $oldUserCount = count($userRepository->findAll());

        $form = $crawler->selectButton('Ajouter')->form();
        $username = 'newUsername';
        $password = 'newPassword!5548';
        $form['user[username]'] = $username;
        $form['user[password][first]'] = $password;
        $form['user[password][second]'] = $password;
        $form['user[email]'] = 'user@gmail.com';
        $form['user[role]'] = 'ROLE_USER';
        $this->client->submit($form);

        $newUserCount = count($userRepository->findAll());
        $user = $userRepository->findOneBy([
            'username' => $username
        ]);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
        $this->assertEquals(
            $oldUserCount + 1,
            $newUserCount
        );
        $this->assertNotNull($user);
    }

    public function testUserEditAnonymous()
    {
        $user = $this->getUser();

        $this->client->request('GET', '/users/'.$user->getId().'/edit');

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

    public function testUserEditUnauthorized()
    {
        $this->logInUser();

        $user = $this->getUser();

        $this->client->request('GET', '/users/'.$user->getId().'/edit');

        $this->assertEquals(
            Response::HTTP_FORBIDDEN,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testUserEditAuthorized()
    {
        $this->logInAdmin();

        $user = $this->getUser();

        $this->client->request('GET', '/users/'.$user->getId().'/edit');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testUserEditInvalidParameters()
    {
        $this->logInAdmin();

        $user = $this->getUser();

        $crawler = $this->client->request('GET', '/users/'.$user->getId().'/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[email]'] = 'invalid_email';
        $this->client->submit($form);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );

        $this->logOut();
    }

    public function testUserEditValidParameters()
    {
        $this->logInAdmin();

        $user = $this->getUser();
        $oldUsername = 'oldUsername';
        $newUsername = 'newUsername';
        $password = 'password!48848M';
        $user->setUsername($oldUsername);
        $this->em->persist($user);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/users/'.$user->getId().'/edit');

        $form = $crawler->selectButton('Modifier')->form();

        $form['user[username]'] = $newUsername;
        $form['user[password][first]'] = $password;
        $form['user[password][second]'] = $password;
        $this->client->submit($form);

        $this->assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );

        $this->em->refresh($user);

        $this->assertEquals(
            $newUsername,
            $user->getUsername()
        );

        $this->logOut();
    }
}