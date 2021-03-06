<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Form\Handler\TaskFormHandler;
use AppBundle\Form\TaskType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends Controller
{
    /**
     * @Route("/tasks", name="task_list")
     * @Security("is_granted('ROLE_USER')")
     */
    public function listAction()
    {
        return $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository('AppBundle:Task')->findAll()]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * @Security("is_granted('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        $user = $this->getUser();
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $formHandler = new TaskFormHandler($this->get("router"), $this->get("doctrine.orm.entity_manager"), $user);
        $response = $formHandler->handle($form, $request);

        if ($response instanceof RedirectResponse) {
            $this->addFlash('success', "La tâche a bien été ajouté.");
            return $response;
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @Security("is_granted('ROLE_USER')")
     */
    public function editAction(Task $task, Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(TaskType::class, $task);

        $formHandler = new TaskFormHandler($this->get("router"), $this->get("doctrine.orm.entity_manager"), $user);
        $response = $formHandler->handle($form, $request);

        if ($response instanceof RedirectResponse) {
            $this->addFlash('success', "La tâche a bien été modifiée.");
            return $response;
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @Security("is_granted('ROLE_USER')")
     */
    public function toggleTaskAction(Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @Security("is_granted('ROLE_USER')")
     */
    public function deleteTaskAction(Task $task)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
