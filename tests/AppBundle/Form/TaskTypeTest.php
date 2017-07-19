<?php

namespace AppBundle\Form;

use AppBundle\Entity\Task;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class TaskTypeTest
 * @package AppBundle\Form
 *
 * @group unit
 */
class TaskTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'title' => "Unit_test_task_". uniqid(),
            'content' => "Some content",
        );

        $form = $this->factory->create(TaskType::class);

        $task = new Task();
        $task->setTitle($formData["title"]);
        $task->setContent($formData["content"]);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        foreach ($form->getData() as $proprety => $value) {
            $method = "get". ucfirst($proprety);
            $this->assertEquals($task->$method(), $value);
        }

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
