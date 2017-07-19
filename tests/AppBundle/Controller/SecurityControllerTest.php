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
    protected $admin_username = "test_admin";
    protected $user_username = "test_user";
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
            '_username' => $this->admin_username,
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

    public function testShouldntAccessUserProtectedPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'tasks');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testShouldntAccessAdminProtectedPage()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => $this->user_username,
            'PHP_AUTH_PW'   => $this->password,
        ));

        $crawler = $client->request('GET', 'users');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}