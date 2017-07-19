<?php
/**
 * Created by PhpStorm.
 * User: cachwir
 * Date: 11/07/17
 * Time: 12:25
 */

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TaskControllerTest
 * @package AppBundle\Tests\Controller
 *
 * @group functional
 */
class TaskControllerTest extends WebTestCase
{
    protected $title = "Test_Functionnal_Task";
    protected $author_id = 1;

    protected $user_username = "test_user";
    protected $password = "password";

    /**
     * @var Client $client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => $this->user_username,
            'PHP_AUTH_PW'   => $this->password,
        ));
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', 'tasks');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter(".task")->count());
    }

    public function testAdd()
    {
        $crawler = $this->client->request('GET', 'tasks/create');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter("form"));
    }

    public function testShouldSaveNewTask(Task $task = null)
    {
        $csrfToken = $this->client->getContainer()
            ->get('security.csrf.token_manager')
            ->getToken('task');

        if ($task === null) {
            $action = "tasks/create";
        } else {
            $action = "tasks/" . $task->getId() . "/edit";
        }

        $crawler = $this->client->request('POST', $action, [
            'task' => [
                'title' => $this->title,
                'content' => "This is a test task. Don't mind it.",
                '_token' => $csrfToken,
            ]
        ]);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $crawler = $this->client->followRedirect();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertRegExp("~$this->title~", $crawler->filter(".task:last-child")->text());
    }

    public function testEdit()
    {
        $task = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:Task")->findOneBy(["title" => $this->title]);

        if (!$task instanceof Task) {
            throw new \Error("The task ". $this->title . " cannot be edited as it doesn't exist in the database.");
        }

        $crawler = $this->client->request('GET', 'tasks/'. $task->getId() .'/edit');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter("form"));
    }

    public function testShouldEditTask()
    {
        $task = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:Task")->findOneBy(["title" => $this->title]);

        if (!$task instanceof Task) {
            throw new \Error("The task " . $this->title . " cannot be edited as it doesn't exist in the database.");
        }

        $this->testShouldSaveNewTask($task);
    }

    public function testToggle()
    {
        $task = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:Task")->findOneBy(["title" => $this->title]);

        if (!$task instanceof Task) {
            throw new \Error("The task " . $this->title . " cannot be toggled as it doesn't exist in the database.");
        }

        $old_is_done = $task->isDone();

        $crawler = $this->client->request('GET', 'tasks/' . $task->getId() . '/toggle');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertNotEquals($task->isDone(), $old_is_done);
    }

    public function testDelete()
    {
        $task = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:Task")->findOneBy(["title" => $this->title]);

        if (!$task instanceof Task) {
            throw new \Error("The task " . $this->title . " cannot be toggled as it doesn't exist in the database.");
        }

        $crawler = $this->client->request('GET', 'tasks/' . $task->getId() . '/delete');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $crawler = $this->client->followRedirect();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertNull($task->getId());
    }
}