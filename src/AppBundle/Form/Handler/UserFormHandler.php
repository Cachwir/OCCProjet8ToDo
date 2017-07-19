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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFormHandler
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
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(RouterInterface $router, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->router = $router;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function handle(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $this->em->persist($user);
            $this->em->flush();

            return new RedirectResponse($this->router->generate('user_list'));
        }

        return $form;
    }
}