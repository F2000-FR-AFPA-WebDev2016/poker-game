<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserController extends Controller
{
    /**
     * @Route("/register", name="_register")
     */
    public function registerAction()
    {
        return $this->render('AfpaPokerGameBundle:User:register.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/login", name="_login")
     */
    public function loginAction()
    {
        return $this->render('AfpaPokerGameBundle:User:login.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/logout")
     */
    public function logoutAction()
    {
        return $this->render('AfpaPokerGameBundle:User:logout.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/account")
     */
    public function accountAction()
    {
        return $this->render('AfpaPokerGameBundle:User:account.html.twig', array(
            // ...
        ));
    }

}
