<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Afpa\PokerGameBundle\Entity\User;
use Afpa\PokerGameBundle\Entity\TablePoker;
use Afpa\PokerGameBundle\Models\Player;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


class TablePokerController extends Controller {

    private $inscriptionTable;
    
    /**
     * @Route("/listTable", name="_list_table")
     */
    public function listTableAction(Request $request) {
        $oSession = $request->getSession();

        if (!$oSession->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }
        
        //Liste des tables en attente
        
        $em = $this->getDoctrine()->getManager();
        $aPendingTables = $em->getRepository('AfpaPokerGameBundle:TablePoker')->findAll();
        $nb = count($aPendingTables) == 0 ? 4 : 4 - count($aPendingTables);
        if($nb > 0){
            for($i = 0; $i < $nb; $i++){
                $oTablePoker = new TablePoker();
                $oTablePoker->setFactor(1);
                $oTablePoker->setInitialBet(1);
                $oTablePoker->setNbPosition(2);
                $oTablePoker->setTimeLevel(1);
                $em->persist($oTablePoker);
                $em->flush();
                $aPendingTables[] = $oTablePoker;
            }
        }
        
        foreach ($aPendingTables as $key => $value){
            $userInscrit = false;
            $user = $oSession->get('user')->getId();
            $array = is_array(unserialize($value->getPlayerList())) ? unserialize($value->getPlayerList()) : array();
            foreach($array as $val){
                if($val->getIdPlayer() == $user){
                    $userInscrit = true;
                    break;
                }
            }
            $verifNbInscrit = count($array);
            if($verifNbInscrit == 2 && $userInscrit == false){
                $form = $this->createFormBuilder()
                    ->add('id', HiddenType::class, array('data' => $value->getId()))
                    ->getForm();
            }else{
                $form = $this->createFormBuilder()
                    ->add('id', HiddenType::class, array('data' => $value->getId()))
                    ->add('action', HiddenType::class, array('data' => $userInscrit == false ? 'in' : 'out' ))
                    ->add('inscription', SubmitType::class, array('label' => $userInscrit == false ? 'S\'inscrire' : 'Se désinscrire'))
                    ->getForm();
            }
            $form->handleRequest($request);
            
            $aPendingTables[$key] = array('form' => $form->createView(), 'table' => $value, );
            
            
            if ($form->isSubmitted() && $form->isValid()) {
                dump($form);die();
                
                
                
                $this->inscriptionTable = $form->getNormData()['id'];
            }
        }
        
        if($this->inscriptionTable != null){
            $table = $em->getRepository('AfpaPokerGameBundle:TablePoker')->findOneById($this->inscriptionTable);
            $oPlayer = new Player($oSession->get('user'));
            
            $array = unserialize($table->getPlayerList());
            $array[] = $oPlayer;
            $table->setPlayerList(serialize($array));
            $em->flush();
        }
        
       /*
        for($i= 0; $i <= count($aPendingTables); $i++){
            $form = $this->createFormBuilder()
                ->add('inscription', SubmitType::class, array('label' => 'S\'inscrire'))
                ->getForm();
            $form->handleRequest($request);
            $listForm[] = $form->createView();
        }*/
        //creation formulaire inscription table
        
        
        return $this->render('AfpaPokerGameBundle:TablePoker:list_table.html.twig', array(
                    'pendingTables' => $aPendingTables,
        ));
    }
    
    
    public function verifInscription(){
        
    }
    
    

    /**
     * @Route("/play/{idTable}", name="_play")
     */
    public function play($idTable, Request $request) {
        //test user connecté
        $oSession = $request->getSession();
        if (!$oSession->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }

        //génération du player
        $oPlayer = new Player($oSession->get('user'));


        //Mise à jour de TablePoker
        $repo = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:TablePoker');
        $oTablePoker = $repo->find($idTable);
        $iNbPosition = $oTablePoker->getNbPosition();
        $aListPlayer = unserialize($oTablePoker->getPlayerList());
        $aListPlayer[$oPlayer->getIdPlayer()] = $oPlayer;
        $nbPlayer = count($aListPlayer);

        if (count($aListPlayer) <= $iNbPosition) {

            $oTablePoker->setPlayerList(serialize($aListPlayer));
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->render('AfpaPokerGameBundle:TablePoker:play.html.twig', array(
                        'listPlayer' => $aListPlayer,
                        'idTable' => $idTable
            ));
        } else {
            // si le user est un player de la table, on prend la route play
            if (isset($aListPlayer[$oSession->get('user')->getIdPlayer()])) {

                return $this->render('AfpaPokerGameBundle:TablePoker:play.html.twig', array(
                            'listPlayer' => $aListPlayer,
                            'idTable' => $idTable
                ));
            }
            //sinon on renvoie sur la liste des tables
            return $this->redirect($this->generateUrl('list_table'));
        }
    }

    /**
     * @Route("/view/{idTable}", name="_game_view")
     */
    public function gameViewAction($idTable, Request $request) {
        //test user connecté
        $oSession = $request->getSession();
        if (!$oSession->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }

        //génération du player
        $oPlayer = new Player($oSession->get('user'));


        //Mise à jour de TablePoker
        $repo = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:TablePoker');
        $oTablePoker = $repo->find($idTable);
        $iNbPosition = $oTablePoker->getNbPosition();
        $aListPlayer = unserialize($oTablePoker->getPlayerList());
        $nbPlayer = count($aListPlayer);

        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    'listPlayer' => $aListPlayer
        ));
    }

}
