<?php

namespace Tests\AppBundle\Form\Handler;

use AppBundle\Entity\User;
use AppBundle\Form\Handler\UserFormHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserFormHandlerTest
 * @package Tests\AppBundle\Form
 *
 * @group unit
 */
class UserFormHandlerTest extends TestCase
{
    /**
     * @var UserFormHandler
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

    /**
     * @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
     */
    protected $passwordEncoder;


    public function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->passwordEncoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->handler = new UserFormHandler($this->router, $this->em, $this->passwordEncoder);
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

        $user = new User();

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
            ->with($this->equalTo('user_list'))
            ->will($this->returnValue('/users'))
            ;
        $this->passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->with($this->identicalTo($user), $this->identicalTo($user->getPassword()))
            ->will($this->returnValue("encoded_password"))
            ;

        $response = $this->handler->handle($form, $request);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $response
        );
        $this->assertEquals('/users', $response->getTargetUrl());

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
