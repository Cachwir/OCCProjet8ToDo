<?php

namespace Tests\AppBundle\Form\Handler;

use AppBundle\Entity\Task;
use AppBundle\Form\Handler\TaskFormHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TaskFormHandlerTest
 * @package Tests\AppBundle\Form
 *
 * @group unit
 */
class TaskFormHandlerTest extends TestCase
{
    /**
     * @var TaskFormHandler
     */
    protected $handler;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;


    public function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->handler = new TaskFormHandler($this->router, $this->em);
    }

    public function testShouldHandleAnUnsuccessfulFormSubmission()
    {
        $request = new Request();

        $form = $this->getFormMock();

        $this->assertEquals($form, $this->handler->handle($form, $request));

    }

    public function testShouldHandleASuccessfulFormSubmission()
    {
        $request = new Request();

        $user = new Task();

        $form = $this->getFormMock();
        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true))
        ;
        $form->expects($this->once())
            ->method('isSubmitted')
            ->will($this->returnValue(true))
        ;
        $form->expects($this->once())
            ->method('handleRequest')
            ->with($this->identicalTo($request))
        ;
        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($user))
        ;
        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($user))
        ;
        $this->em->expects($this->once())
            ->method('flush')
        ;
        $this->router->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('task_list'))
            ->will($this->returnValue('/tasks'))
            ;

        $response = $this->handler->handle($form, $request);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $response
        );
        $this->assertEquals('/tasks', $response->getTargetUrl());

    }

    public function getFormMock()
    {
        return $this
            ->getMockBuilder("Symfony\Component\Form\Test\FormInterface")
            ->disableOriginalConstructor()
            ->getMock()
            ;
    }
}
