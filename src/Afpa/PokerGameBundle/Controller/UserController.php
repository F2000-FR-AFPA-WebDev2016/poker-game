<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Afpa\PokerGameBundle\Entity\User;
use Afpa\PokerGameBundle\Entity\TablePoker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Afpa\PokerGameBundle\Models\Encrypt;
use Symfony\Component\HttpFoundation\Session\Session;
use Afpa\PokerGameBundle\Entity\UserModels;

//use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;

class UserController extends Controller {

    /**
     * @Route("/register", name="_register")
     */
    public function registerAction(Request $request) {
        $oUser = new User();
        
        //effacement des ancien message flash
        $request->getSession()->getBag('flashes')->clear();

        $form = $this->formLogin();

        //Création du formulaire d'inscription
        $oForm = $this->formInscription($oUser);

        $oForm->handleRequest($request);
        
        $result = $this->verifRegister($oForm, $oUser);

        if($result != false){
            return $this->redirectToRoute($result);
        }
        
        return $this->render('AfpaPokerGameBundle:User:register.html.twig', array(
                    'form' => $oForm->createView(),
                    'form2' => $form->createView(),
        ));
    }

    /**
     * @Route("/login", name="_login")
     */
    public function loginAction(Request $request) {
        
        $oUser = new User();
        
        $oFormLogin = $this->formLogin($oUser);

        $oFormLogin->handleRequest($request);
        
        $result = $this->verifLogin($oFormLogin, $request, $oUser);
        
        if($result != false){
            return $this->redirectToRoute($result);
        }
        
        return $this->render('AfpaPokerGameBundle:User:login.html.twig', array(
                    'form' => $oFormLogin->createView()
        ));
    }

    /**
     * @Route("/logout" , name="_logout")
     */
    public function logoutAction(Request $request) {
        
        $request->getSession()->getBag('flashes')->clear();
        $request->getSession()->clear();
        
        return $this->redirectToRoute('_home');
        // return $this->render('AfpaPokerGameBundle:User:logout.html.twig', array(
        //                // ...
        //));
    }

    /**
     * @Route("/account", name="_account")
     */
    public function accountAction(Request $request) {
        $user = $request->getSession()->get('user');
        
        if(!$user){
            return $this->redirectToRoute('_home');
        }
        if($user->getAvatar() == ''){
            $user->setAvatar('avatar_null.jpg');
        }
        

        //effacement des ancien message flash
        $request->getSession()->getBag('flashes')->clear();
        $oUser = new User();
        
        $formPseudo = $this->createFormBuilder($oUser)
                ->add('pseudo', TextType::class, array('attr' => array('placeholder' => $user->getPseudo())))
                ->add('pseudoSave', SubmitType::class, array('label' => 'Modifier'))
                ->getForm();
        $formMail = $this->createFormBuilder($oUser)
                ->add('mail', EmailType::class, array('attr' => array('placeholder' => $user->getMail())))
                ->add('mailSave', SubmitType::class, array('label' => 'Modifier'))
                ->getForm();
        $formPassword = $this->createFormBuilder($oUser)
                ->add('password', PasswordType::class, array('attr' => array('placeholder' => 'Nouveau mot de passe')))
                ->add('passwordSave', SubmitType::class, array('label' => 'Modifier'))
                ->getForm();
        $formImage = $this->createFormBuilder($oUser)
                ->add('avatar', fileType::class, array('attr' => array('placeholder' => 'Changer l\'avatar')))
                ->add('imageSave', SubmitType::class, array('label' => 'Envoyer'))
                ->getForm();
        $formMoney = $this->createFormBuilder($oUser)
                ->add('moneySave', SubmitType::class, array('label' => 'Recharger'))
                ->getForm();
        
        $formPseudo->handleRequest($request);
        $formMail->handleRequest($request);
        $formPassword->handleRequest($request);
        $formMoney->handleRequest($request);
        $formImage->handleRequest($request);
        
        if ($formPseudo->isSubmitted() && $formPseudo->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $verifUser = $em->getRepository('AfpaPokerGameBundle:User')->findOneByPseudo($oUser->getPseudo());
            
            if(!$verifUser){
                $changePseudo = $em->getRepository('AfpaPokerGameBundle:User')->findOneByPseudo($user->getPseudo());
                $user->setPseudo($oUser->getPseudo());
                $changePseudo->setPseudo($oUser->getPseudo());
                $em->flush();
                $this->addFlash('success_account', 'Votre pseudo a été modifié');
                $user->setPseudo($oUser->getPseudo());
                $request->getSession()->set('user', $user);
            }else{
                $this->addFlash('warning_account', 'Ce pseudo est déjà utilisé');
            }
        }
        
        if ($formMail->isSubmitted() && $formMail->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $verifMail = $em->getRepository('AfpaPokerGameBundle:User')->findOneByMail($oUser->getMail());
            
            if(!$verifMail){
                $changeMail = $em->getRepository('AfpaPokerGameBundle:User')->findOneByMail($user->getMail());
                $user->setMail($oUser->getMail());
                
                $changeMail->setMail($oUser->getMail());
                
                $em->flush();
                $this->addFlash('success_account', 'Votre mail a été modifié');
                $request->getSession()->set('user', $user);
            }else{
                $this->addFlash('warning_account', 'Ce mail est déjà utilisé');
            }
        }
        
        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $changePass = $em->getRepository('AfpaPokerGameBundle:User')->findOneByPseudo($user->getPseudo());
            $oEncrypt = new Encrypt($oUser->getPassword());
            $oUser->setPassword($oEncrypt->getEncryption());
            $user->setPassword($oUser->getPassword());


            $changePass->setPassword($oUser->getPassword());
            $em->flush();
            $this->addFlash('success_account', 'Votre mot de passe a été modifié');
            $request->getSession()->set('user', $user);
        }
        
        if ($formMoney->isSubmitted() && $formMoney->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $verifMoney = $em->getRepository('AfpaPokerGameBundle:User')->findOneByPseudo($user->getPseudo());
            
            if($verifMoney->getVirtualMoney() < 300.0){
                $date = $verifMoney->getTimeLastCredit();
                $now = new \DateTime('now');
                $interval = intval($now->diff($date)->format('%h'));
                if($interval > 1){
                    $user->setVirtualMoney(300.0);

                    $verifMoney->setVirtualMoney(300.0);
                    $verifMoney->setTimeLastCredit($now);

                    $em->flush();
                    $this->addFlash('success_account', 'Votre monnaie a été crédité');
                    $request->getSession()->set('user', $user);
                }else{
                    $this->addFlash('warning_account', 'Vous devez attendre 1 heure entre chaque recharge');
                }
            }else{
                $this->addFlash('warning_account', 'Votre monnaie est déjà au maximum');
            }
        }
        
        if ($formImage->isSubmitted() && $formImage->isValid()) {
            
            $extFile = strtolower($oUser->getAvatar()->guessExtension());
            if($extFile == 'png' | $extFile == 'jpg' | $extFile == 'jpeg' | $extFile == 'gif' ){
                $fileName = md5(uniqid()).'.'.$oUser->getAvatar()->guessExtension();
                
                $oUser->getAvatar()->move( str_replace('app', 'web/env/images/profil', $this->get('kernel')->getRootDir()),$fileName);
                $em = $this->getDoctrine()->getManager();
                $changeAvatar = $em->getRepository('AfpaPokerGameBundle:User')->findOneByPseudo($user->getPseudo());
                
                if($user->getAvatar() != 'avatar_null.jpg'){
                    unlink(str_replace('app', 'web/env/images/profil/'.$changeAvatar->getAvatar(), $this->get('kernel')->getRootDir()));
                }
                
                $user->setAvatar($fileName);
                $changeAvatar->setAvatar($user->getAvatar());
                $em->flush();
                $this->addFlash('succes_account', 'votre avatar a été modifié');
            }else{
                $this->addFlash('warning_account', 'Merci de mettre une image valide');
            }
                
        }
        
        return $this->render('AfpaPokerGameBundle:User:account.html.twig', array(
            'user' => $user,
            'formPseudo' => $formPseudo->createView(),
            'formMail' => $formMail->createView(),
            'formPass' => $formPassword->createView(),
            'formMoney' => $formMoney->createView(),
            'formImage' => $formImage->createView()
        ));
    }
    
    public function formLogin($oUser = null){
        $form = $this->createFormBuilder($oUser)
                ->setMethod('POST')
                ->add('mail', TextType::class, array('attr' => array('placeholder' => 'Votre mail')))
                ->add('password', PasswordType::class, array('attr' => array('placeholder' => 'Votre mot de passe')))
                ->add('save', SubmitType::class, array('label' => 'Se connecter'))
                ->getForm();
        
        return $form;
    }
    
    public function formInscription($oUser){
        $form = $this->createFormBuilder($oUser)
                ->add('pseudo', TextType::class, array('required' => true, 'attr' => array('placeholder' => 'Choisissez un pseudo')))
                ->add('password', PasswordType::class, array('required' => true, 'attr' => array('placeholder' => 'Choisissez un mot passe')))
                ->add('mail', EmailType::class, array('required' => true, 'attr' => array('placeholder' => 'Saisir votre e-mail')))
                ->add('submit', SubmitType::class, array('label' => 'OK'))
                ->getForm();
        
        return $form;
    }
    
    public function verifRegister($oForm, $oUser){
        // Si le formulaire est valid on stockera l'utilisateur dans la table User
        if ($oForm->isSubmitted() && $oForm->isValid()) {
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
                return '_account';
            } else {
                // Si le pseudo ou le mail existe déjà dans User
                $this->addFlash('warning', 'Il est possible que vous soyez déjà inscrit, sinon choisissez un autre pseudo');
            }
        }else{
            $this->addFlash('notice', 'Vous êtes sur le point de vous enregistrer sur le site Poker Game');
        }
    }
    
    public function verifLogin($oFormLogin, $request, $oUser){
        if ($oFormLogin->isSubmitted() && $oFormLogin->isValid()) {
            $request->getSession()->getBag('flashes')->clear();
            $repo = $this->getdoctrine()->getRepository('AfpaPokerGameBundle:User');
            $oUserTest = $repo->findOneByMail($oUser->getMail());
            $oEncryptPwd = new Encrypt($oUser->getPassword());
            if ($oUserTest && $oUserTest->getPassword() === $oEncryptPwd->getEncryption()) {
                $player = $this->getdoctrine()->getManager()->getRepository('AfpaPokerGameBundle:Player')->findByUser($oUserTest->getId());
                $session = new Session();
                if(count($player) > 0){
                    foreach($player as $value){
                        $array[$value->getTablePoker()->getId()] = $value;
                    }
                $session->set('partie', $array);
                }
                $session->set('user', $oUserTest);
                $this->addFlash('warning_connect', 'Vous êtes connecté');
                
                return '_account';
            }else{
                $this->addFlash('warning_connect', 'Les identifiants entrés sont incorrects !');
                return '_home';
            }
        }elseif($oFormLogin->isSubmitted() && !$oFormLogin->isValid()){
                $request->getSession()->getBag('flashes')->clear();
                $this->addFlash('warning_connect', 'Les identifiants entrés sont incorrects');
            return '_home';
        }
        return false;
    }

}
