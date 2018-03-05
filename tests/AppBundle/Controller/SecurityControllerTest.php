<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\Fixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SecurityControllerTest extends WebTestCase
{
    const USER_USERNAME = 'user';

    const USER_PASSWORD = 'user';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityManager
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

    public function testLogin()
    {
        $this->client->request('GET', '/login');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testLoginInvalidParameters()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'foo';
        $form['_password'] = 'bar';
        $this->client->submit($form);

        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );
        $this->assertRegExp(
            '#/login$#',
            $response->headers->get('Location')
        );

        $crawler = $this->client->followRedirect();

        $this->assertGreaterThanOrEqual(
            1,
            $crawler->filter('.alert-danger')->count()
        );
    }

    public function testLoginValidParameters()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = self::USER_USERNAME;
        $form['_password'] = self::USER_PASSWORD;
        $this->client->submit($form);

        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );

        $this->client->followRedirect();

        $token = $this->client->getContainer()
            ->get('session')
            ->get('_security_main');

        $this->assertNotEmpty($token);

        $token = unserialize($token);
        $this->assertTrue(
            is_a($token, UsernamePasswordToken::class)
        );
        $this->assertEquals(
            'user',
            $token->getUser()->getUsername()
        );
    }
}