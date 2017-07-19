<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\Handler\UserFormHandler;
use AppBundle\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @Route("/users", name="user_list")
     */
    public function listAction()
    {
        return $this->render('user/list.html.twig', ['users' => $this->getDoctrine()->getRepository('AppBundle:User')->findAll()]);
    }

    /**
     * @Route("/users/create", name="user_create")
     */
    public function createAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $formHandler = new UserFormHandler($this->get("router"), $this->get("doctrine.orm.entity_manager"), $this->get("security.password_encoder"));
        $response = $formHandler->handle($form, $request);

        if ($response instanceof RedirectResponse) {
            $this->addFlash('success', "L'utilisateur a bien été ajouté.");
            return $response;
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     */
    public function editAction(User $user, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);

        $formHandler = new UserFormHandler($this->get("router"), $this->get("doctrine.orm.entity_manager"), $this->get("security.password_encoder"));
        $response = $formHandler->handle($form, $request);

        if ($response instanceof RedirectResponse) {
            $this->addFlash('success', "L'utilisateur a bien été modifié.");
            return $response;
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
