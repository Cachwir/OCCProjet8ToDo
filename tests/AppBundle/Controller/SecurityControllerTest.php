<?php
/**
 * Created by PhpStorm.
 * User: cachwir
 * Date: 11/07/17
 * Time: 12:25
 */

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest
 * @package AppBundle\Tests\Controller
 *
 * @group functional
 */
class SecurityControllerTest extends WebTestCase
{
    protected $username = "test";
    protected $password = "password";

    public function testLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', 'login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter("form"));
    }

    public function testLoginCheck()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', 'login_check', [
            '_username' => $this->username,
            '_password' => $this->password,
        ]);

        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertNotEquals(
            $client->getContainer()->get("router")->generate("login", [], Router::ABSOLUTE_URL),
            $client->getRequest()->getUri(),
            $crawler->filter(".alert")->count() > 0 ? $crawler->filter(".alert")->text() : $client->getResponse()->getContent());
    }
}