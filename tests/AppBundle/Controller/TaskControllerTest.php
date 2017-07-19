<?php
/**
 * Created by PhpStorm.
 * User: cachwir
 * Date: 11/07/17
 * Time: 12:25
 */

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\Task;
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

    public function testList()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'password',
        ));

        $crawler = $client->request('GET', 'tasks');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter(".task")->count());
    }

    public function testAdd()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'password',
        ));

        $crawler = $client->request('GET', 'tasks/create');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter("form"));
    }

    public function testShouldSaveNewTask(Task $task = null)
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'password',
        ));

        $csrfToken = $client->getContainer()
            ->get('security.csrf.token_manager')
            ->getToken('task');

        if ($task === null) {
            $action = "tasks/create";
        } else {
            $action = "tasks/" . $task->getId() . "/edit";
        }

        $crawler = $client->request('POST', $action, [
            'task' => [
                'title' => $this->title,
                'content' => "This is a test task. Don't mind it.",
                '_token' => $csrfToken,
            ]
        ]);

        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertRegExp("~$this->title~", $crawler->filter(".task:last-child")->text());
    }

    public function testEdit()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'password',
        ));

        $task = $client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:Task")->findOneBy(["title" => $this->title]);

        if (!$task instanceof Task) {
            throw new \Error("The task ". $this->title . " cannot be edited as it doesn't exist in the database.");
        }

        $crawler = $client->request('GET', 'tasks/'. $task->getId() .'/edit');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter("form"));
    }

    public function testShouldEditTask()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'password',
        ));

        $task = $client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:Task")->findOneBy(["title" => $this->title]);

        if (!$task instanceof Task) {
            throw new \Error("The task " . $this->title . " cannot be edited as it doesn't exist in the database.");
        }

        $this->testShouldSaveNewTask($task);
    }

    public function testToggle()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'password',
        ));

        $task = $client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:Task")->findOneBy(["title" => $this->title]);

        if (!$task instanceof Task) {
            throw new \Error("The task " . $this->title . " cannot be toggled as it doesn't exist in the database.");
        }

        $old_is_done = $task->isDone();

        $crawler = $client->request('GET', 'tasks/' . $task->getId() . '/toggle');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertNotEquals($task->isDone(), $old_is_done);
    }

    public function testDelete()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'password',
        ));

        $task = $client->getContainer()->get("doctrine.orm.entity_manager")->getRepository("AppBundle:Task")->findOneBy(["title" => $this->title]);

        if (!$task instanceof Task) {
            throw new \Error("The task " . $this->title . " cannot be toggled as it doesn't exist in the database.");
        }

        $crawler = $client->request('GET', 'tasks/' . $task->getId() . '/delete');

        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertNull($task->getId());
    }
}