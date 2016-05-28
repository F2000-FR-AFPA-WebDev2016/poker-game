<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class DefaultController extends Controller {

    /**
     * @Route("/")
     */
    public function indexAction() {
        $form = $this->createFormBuilder()
                ->setMethod('POST')
                ->add('mail', TextType::class, array('attr' => array('placeholder' => 'Votre mail')))
                ->add('password', PasswordType::class, array('attr' => array('placeholder' => 'Votre mot de passe')))
                ->add('save', SubmitType::class, array('label' => 'Se connecter'))
                ->getForm();

        return $this->render('AfpaPokerGameBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

}
