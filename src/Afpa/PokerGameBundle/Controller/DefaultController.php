<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class DefaultController extends Controller {

    /**
     * @Route("/" , name="_home")
     */
    public function indexAction() {
        return $this->render('AfpaPokerGameBundle:Default:index.html.twig');
    }

}
