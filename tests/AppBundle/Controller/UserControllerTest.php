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
    protected static $username;

    public function setUp()
    {
        if (self::$username === null) {
            self::$username = "Test_Functionnal_User_". uniqid();
        }
    }

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

    public function testShouldSaveNewUser(User $user = null)
    {
        $client = static::createClient();

        $csrfToken = $client->getContainer()
            ->get('security.csrf.token_manager')
            ->getToken('user');

        if ($user === null) {
            $action = "users/create";
        } else {
            $action = "users/". $user->getId() ."/edit";
        }

        $crawler = $client->request('POST', $action, [
            'user' => [
                'username' => self::$username,
                'password' => [
                    'first' => 'password2',
                    'second' => 'password2',
                ],
                'email' => "test_". uniqid() . "@gmx.com",
                'role' => User::ROLE_USER,
                '_token' => $csrfToken,
            ]
        ]);

        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertRegExp("~". self::$username. "~", $crawler->filter("tbody tr:last-child")->text());
    }

    public function testEdit()
    {
        $client = static::createClient();

        $user = $client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:User")->findOneBy(["username" => self::$username]);

        if (!$user instanceof User) {
            throw new \Error("The user ". self::$username . " cannot be edited as it doesn't exist in the database.");
        }

        $crawler = $client->request('GET', 'users/'. $user->getId() .'/edit');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter("form"));
    }

    public function testShouldEditUser()
    {
        $client = static::createClient();

        $user = $client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:User")->findOneBy(["username" => self::$username]);

        if (!$user instanceof User) {
            throw new \Error("The user ". self::$username . " cannot be edited as it doesn't exist in the database.");
        }

        $this->testShouldSaveNewUser($user);
    }
}