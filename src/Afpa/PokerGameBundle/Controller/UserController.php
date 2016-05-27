<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Afpa\PokerGameBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserController extends Controller {

    /**
     * @Route("/register", name="_register")
     */
    public function registerAction(Request $request) {
        $oUser = new User();
        $this->addFlash('notice', 'Vous êtes sur le point de vous enregistrer sur le site Poker Game');
        $oForm = $this->createFormBuilder($oUser)
                ->add('pseudo', TextType::class, array('required' => true, 'attr' => array('placeholder' => 'Saisir un pseudo')))
                ->add('password', PasswordType::class, array('required' => true))
                ->add('mail', EmailType::class, array('required' => true))
                ->add('submit', SubmitType::class, array('label' => 'OK'))
                ->getForm();

        if ($request->isMethod('POST')) {
            $oForm->handleRequest($request);

            if ($oForm->isValid()) {
                $repo = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:User');
                $oUserPseudoExist = $repo->findOneByPseudo($oUser->getPseudo());
                $oUserMailExist = $repo->findOneByPseudo($oUser->getMail());
                if (!$oUserPseudoExist && !$oUserMailExist) {
                    $oUser->setVirtualMoney(150);
                    $oUser->setTimeLastCredit(new \DateTime('now'));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($oUser);
                    $em->flush();

                    $this->addFlash('notice', 'Nous sommes heureux de vous compter parmi nos joueurs. Vous pouvez maintenant vous connecter et rejoindre une table de Poker');
                    return $this->redirectToRoute('login');
                } else {

                    $this->addFlash('notice', 'Il est possible que vous soyez déjà inscrit, sinon choisissez un autre pseudo');


                    return $this->render('AfpaPokerGameBundle:User:register.html.twig', array(
                                'form' => $oForm->createView(),
                    ));
                }
            }
        }

        return $this->render('AfpaPokerGameBundle:User:register.html.twig', array(
                    'form' => $oForm->createView(),
        ));
    }

    /**
<<<<<<< HEAD
     * @Route("/login", name="login")
=======
     * @Route("/login", name="_login")
>>>>>>> 66f6c1c79385a2967f6fbed2c0abc3ee1a5db8ad
     */
    public function loginAction(Request $request) {
        //$session = $request->getSession()->clear();
        return $this->render('AfpaPokerGameBundle:User:login.html.twig', array(
                        // ...
        ));
    }

    /**
     * @Route("/logout")
     */
    public function logoutAction() {
        return $this->render('AfpaPokerGameBundle:User:logout.html.twig', array(
                        // ...
        ));
    }

    /**
     * @Route("/account")
     */
    public function accountAction() {
        return $this->render('AfpaPokerGameBundle:User:account.html.twig', array(
                        // ...
        ));
    }

}
