<?php
/**
 * Created by PhpStorm.
 * User: cachwir
 * Date: 19/07/17
 * Time: 12:22
 */

namespace AppBundle\Form\Handler;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class TaskFormHandler
{
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var User
     */
    private $user;

    public function __construct(RouterInterface $router, EntityManagerInterface $em, User $user)
    {
        $this->router = $router;
        $this->em = $em;
        $this->user = $user;
    }

    public function handle(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            if ($task->getId() === null) {
                $task->setAuthor($this->user);
            }

            $this->em->persist($task);
            $this->em->flush();

            return new RedirectResponse($this->router->generate('task_list'));
        }

        return $form;
    }
}