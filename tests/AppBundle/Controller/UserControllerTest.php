<?php
/**
 * Created by PhpStorm.
 * User: cachwir
 * Date: 11/07/17
 * Time: 12:25
 */

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserControllerTest
 * @package AppBundle\Tests\Controller
 *
 * @group functional
 */
class UserControllerTest extends WebTestCase
{
    public function testList()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'users');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter("tbody tr")->count());
    }

    public function testAdd()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'users/create');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter("form"));
    }

    public function testShouldSaveNewUser()
    {
        $client = static::createClient();

        $csrfToken = $client->getContainer()
            ->get('security.csrf.token_manager')
            ->getToken('user');

        $username =  "Test_". uniqid();

        $crawler = $client->request('POST', 'users/create', [
            'user' => [
                'username' => $username,
                'password' => [
                    'first' => 'password',
                    'second' => 'password',
                ],
                'email' => "test_". uniqid() . "@gmx.com",
                '_token' => $csrfToken,
            ]
        ]);

        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertRegExp("~$username~", $crawler->filter("tbody tr:last-child")->text());
    }
}