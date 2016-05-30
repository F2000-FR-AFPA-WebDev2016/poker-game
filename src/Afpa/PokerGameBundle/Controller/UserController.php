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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Afpa\PokerGameBundle\Models\Encrypt;
use Symfony\Component\HttpFoundation\Session\Session;

//use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;

class UserController extends Controller {

    /**
     * @Route("/register", name="_register")
     */
    public function registerAction(Request $request) {
        $oUser = new User();
        //message flash
        $this->addFlash('notice', 'Vous êtes sur le point de vous enregistrer sur le site Poker Game');

        //Création du formulaire d'inscription
        $oForm = $this->createFormBuilder($oUser)
                ->add('pseudo', TextType::class, array('required' => true, 'attr' => array('placeholder' => 'Choisissez un pseudo')))
                ->add('password', PasswordType::class, array('required' => true, 'attr' => array('placeholder' => 'Choisissez un mot passe')))
                ->add('mail', EmailType::class, array('required' => true, 'attr' => array('placeholder' => 'Saisir votre e-mail')))
                ->add('submit', SubmitType::class, array('label' => 'OK'))
                ->getForm();

        // Récupération des données sur méthode POST
        if ($request->isMethod('POST')) {
            $oForm->handleRequest($request);
            // Si le formulaire est valid on stockera l'utilisateur dans la table User
            if ($oForm->isValid()) {
                $repo = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:User');
                $oUserPseudoExist = $repo->findOneByPseudo($oUser->getPseudo());
                $oUserMailExist = $repo->findOneByMail($oUser->getMail());
                //On teste si le pseudo ou le mail est déjà présent dans User
                if (!$oUserPseudoExist && !$oUserMailExist) {
                    $oUser->setVirtualMoney(150);
                    //cryptage du mot de passe avec salt + md5 - class Encrypt
                    $oEncrypt = new Encrypt($oUser->getPassword());
                    $oUser->setPassword($oEncrypt->getEncryption());
                    $oUser->setTimeLastCredit(new \DateTime('now'));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($oUser);
                    $em->flush();


                    // Après l'enregistrement redirection sur la route login avec message flash
                    $this->addFlash('notice', 'Nous sommes heureux de vous compter parmi nos joueurs. Vous pouvez maintenant modifier vos informations personnelles ou et rejoindre une table de Poker');
                    $session = new Session();
                    $session->set('user', $oUser);
                    return $this->redirectToRoute('account');
                } else {
                    // Si le pseudo ou le mail existe déjà dans User
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

        if ($request->isMethod('POST')) {

            /* if ($oFormLogin->isValid()) {
              $repo = $this->getdoctrine()->getRepository('AfpaPokerGameBundle:User');
              } */
        }


//        $oForm = $this->createFormBuilder($oUser)
//                ->add('pseudo', TextType::class, array('required' => true, 'attr' => array('placeholder' => 'Choisissez un pseudo')))
//                ->add('password', PasswordType::class, array('required' => true, 'attr' => array('placeholder' => 'Choisissez un mot passe')))
//                ->add('mail', EmailType::class, array('required' => true, 'attr' => array('placeholder' => 'Saisir votre e-mail')))
//                ->add('avatar', FileType::class, array('attr' => array('placeholder' => 'Envoyer votre avatar')))
//                ->add('submit', SubmitType::class, array('label' => 'OK'))
//                ->getForm();
//        return $this->render('AfpaPokerGameBundle:User:account.html.twig', array(
//                    'form' => $oForm->createView(),
//        ));
    }

    /**
     * @Route("/logout" , name="logout")
     */
    public function logoutAction(Request $request) {
        $session = $request->getSession();
        $session->clear();
        return $this->redirectToRoute('home');
        // return $this->render('AfpaPokerGameBundle:User:logout.html.twig', array(
        //                // ...
        //));
    }

    /**
     * @Route("/account", name="account")
     */
    public function accountAction() {
        return $this->render('AfpaPokerGameBundle:User:account.html.twig', array(
                        // ...
        ));
    }

}
