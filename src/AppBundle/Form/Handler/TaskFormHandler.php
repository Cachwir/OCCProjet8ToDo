<?php
/**
 * Created by PhpStorm.
 * User: cachwir
 * Date: 19/07/17
 * Time: 12:22
 */

namespace AppBundle\Form\Handler;

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

    public function __construct(RouterInterface $router, EntityManagerInterface $em)
    {
        $this->router = $router;
        $this->em = $em;
    }

    public function handle(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($form->getData());
            $this->em->flush();

            return new RedirectResponse($this->router->generate('task_list'));
        }

        return $form;
    }
}