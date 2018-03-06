<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $anonymous;

    /**
     * @var Client
     */
    private $authenticated;

    public function setUp()
    {
        $this->anonymous = self::createClient();
        $this->authenticated = self::createClient();

        $this->logIn($this->authenticated);
    }

    public function tearDown()
    {
        $this->anonymous = null;
        $this->authenticated = null;
    }

    public function logIn(Client $client)
    {
        $session = $client->getKernel()->getContainer()->get('session');

        $firewall = 'main';
        $token = new UsernamePasswordToken('admin', null, $firewall, ['ROLE_ADMIN']);
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    public function testIndexAnonymous()
    {
        $this->anonymous->request('GET', '/');
        $response = $this->anonymous->getResponse();

        $this->assertEquals(
            Response::HTTP_FOUND,
            $response->getStatusCode()
        );
        $this->assertRegExp(
            '#/login$#',
            $response->headers->get('Location')
        );
    }

    public function testIndexAuthenticated()
    {
        $this->authenticated->request('GET', '/');
        $response = $this->authenticated->getResponse();

        $this->assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );
    }
}
