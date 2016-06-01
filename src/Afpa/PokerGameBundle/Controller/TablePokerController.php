<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Afpa\PokerGameBundle\Entity\User;
use Afpa\PokerGameBundle\Entity\TablePoker;
use Afpa\PokerGameBundle\Models\Player;

class TablePokerController extends Controller {

    /**
     * @Route("/listTable", name="_list_table")
     */
    public function listTableAction(Request $request) {
        $oSession = $request->getSession();

        if (!$oSession->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }
//Liste des tables en attente

        $repo = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:TablePoker');
        $aPendingTables = $repo->findAll();
        if ($aPendingTables == array()) {
            $oTablePoker = new TablePoker();
            $oTablePoker->setFactor(1);
            $oTablePoker->setInitialBet(1);
            $oTablePoker->setNbPosition(2);
            $oTablePoker->setTimeLevel(1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($oTablePoker);
            $em->flush();
            $aPendingTables[] = $oTablePoker;
        }



        return $this->render('AfpaPokerGameBundle:TablePoker:list_table.html.twig', array(
                    'pendingTables' => $aPendingTables,
        ));
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
