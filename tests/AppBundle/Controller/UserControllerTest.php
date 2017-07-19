<?php
/**
 * Created by PhpStorm.
 * User: cachwir
 * Date: 11/07/17
 * Time: 12:25
 */

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
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

    protected $admin_username = "test_admin";
    protected $password = "password";

    /**
     * @var Client $client
     */
    protected $client;

    public function setUp()
    {
        if (self::$username === null) {
            self::$username = "Test_Functionnal_User_". uniqid();
        }

        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => $this->admin_username,
            'PHP_AUTH_PW'   => $this->password,
        ));
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', 'users');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter("tbody tr")->count());
    }

    public function testAdd()
    {
        $crawler = $this->client->request('GET', 'users/create');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter("form"));
    }

    public function testShouldSaveNewUser(User $user = null)
    {
        $csrfToken = $this->client->getContainer()
            ->get('security.csrf.token_manager')
            ->getToken('user');

        if ($user === null) {
            $action = "users/create";
        } else {
            $action = "users/". $user->getId() ."/edit";
        }

        $crawler = $this->client->request('POST', $action, [
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

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $crawler = $this->client->followRedirect();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertRegExp("~". self::$username. "~", $crawler->filter("tbody tr:last-child")->text());
    }

    public function testEdit()
    {
        $user = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:User")->findOneBy(["username" => self::$username]);

        if (!$user instanceof User) {
            throw new \Error("The user ". self::$username . " cannot be edited as it doesn't exist in the database.");
        }

        $crawler = $this->client->request('GET', 'users/'. $user->getId() .'/edit');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter("form"));
    }

    public function testShouldEditUser()
    {
        $user = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:User")->findOneBy(["username" => self::$username]);

        if (!$user instanceof User) {
            throw new \Error("The user ". self::$username . " cannot be edited as it doesn't exist in the database.");
        }

        $this->testShouldSaveNewUser($user);
    }
}