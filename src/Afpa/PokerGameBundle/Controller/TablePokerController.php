<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Afpa\PokerGameBundle\Entity\User;
use Afpa\PokerGameBundle\Entity\TablePoker;
use Afpa\PokerGameBundle\Entity\Player;
use Afpa\PokerGameBundle\Models\Card;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;

class TablePokerController extends Controller {

    private $session;
    private $tablePlay;
    private $em;
    private $aPendingTables;
    private $inscriptionTable;
    private $nbTable = 2;
    private $nameTable = array('Heads up', 'Heads up turbo');
    private $tableInit = array(
        'Heads up' => array('nbPos' => 2, 'factor' => 2, 'timeLevel' => 2, 'initialBet' => 15, 'stack' => 5000, 'buyIn' => 5, 'nbInscrit' => 0),
        'Heads up turbo' => array('nbPos' => 2, 'factor' => 2, 'timeLevel' => 1, 'initialBet' => 100, 'stack' => 1500, 'buyIn' => 10, 'nbInscrit' => 0));

    /**
     * @Route("/listTable", name="_list_table")
     */
    public function listTableAction(Request $request) {
        $this->session = $request->getSession();
        if (!$this->session->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }

        if ($request->getMethod('POST')) {
            $this->prepareTable($request);
        }
        return $this->render('AfpaPokerGameBundle:TablePoker:list_table.html.twig');
    }

    /**
     * @Route("/listTableRefresh", name="_list_table_refresh")
     */
    public function listTableRefreshAction(Request $request) {
        $this->session = $request->getSession();
        if (!$this->session->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }

        $this->prepareTable($request);

        return $this->render('AfpaPokerGameBundle:TablePoker:list_table_refresh.html.twig', array(
                    'pendingTables' => $this->aPendingTables,
        ));
    }

    /**
     * @Route("/listPartie", name="_list_partie")
     */
    public function listPartieAction(Request $request) {
        $this->session = $request->getSession();
        if (!$this->session->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }
        $this->tablePlay = true;

        return $this->render('AfpaPokerGameBundle:TablePoker:list_partie.html.twig');
    }

    /**
     * @Route("/listPartieRefresh", name="_list_partie_refresh")
     */
    public function listPartieRefreshAction(Request $request) {
        $this->session = $request->getSession();
        if (!$this->session->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }
        $this->tablePlay = true;
        $this->prepareTable($request);
        return $this->render('AfpaPokerGameBundle:TablePoker:list_partie_refresh.html.twig', array(
                    'pendingTables' => $this->aPendingTables,
        ));
    }

    public function prepareTable(Request $request) {
        $this->initialiseTable($request);
        if ($this->inscriptionTable != null) {
            $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->findOneById($this->inscriptionTable['idTable']);
            $nbInscrit = $table->getNbInscrit();
            $user = $this->em->getRepository('AfpaPokerGameBundle:User')->findOneById($this->session->get('user')->getId());
            $player = new Player();
            $player->setTablePoker($table);
            $player->setUser($user);
//modif michel
            $player->setPosition($nbInscrit + 1);
//
            $playerTable = $this->em->getRepository('AfpaPokerGameBundle:Player')->findByTablePoker($this->inscriptionTable['idTable']);

            $userExist = false;
            foreach ($playerTable as $onePlayer) {
                if ($onePlayer->getUser()->getId() == $user->getId()) {
                    $userExist = true;
                    break;
                }
            }

            if ($userExist == false && $this->inscriptionTable['action'] == 'in') {
                $this->miseAJourPlayerCredit($request, $user->getId(), $table->getBuyIn() * -1);
                $table->setNbInscrit( ++$nbInscrit);
                $this->em->persist($player);
                $this->em->flush();
                if (!$this->session->get('partie')) {
                    $this->session->set('partie', array($this->inscriptionTable['idTable'] => $player));
                } else {
                    $partie = $this->session->get('partie');
                    $partie[$this->inscriptionTable['idTable']] = $player;
                    $this->session->set('partie', $partie);
                }
            } elseif ($userExist == true && $this->inscriptionTable['action'] == 'out') {
                $this->miseAJourPlayerCredit($request, $user->getId(), $table->getBuyIn());
                $table->setNbInscrit( --$nbInscrit);
                $this->em->remove($onePlayer);
                $this->em->flush();
                if (count($this->session->get('partie')) < 2) {

                    $this->session->remove('partie');
                } else {
                    $partie = $this->session->get('partie');
                    unset($partie[$this->inscriptionTable['idTable']]);
                    $this->session->set('partie', $partie);
                }
            }
        }
    }

    public function initialiseTable(Request $request) {
        $this->em = $this->getDoctrine()->getManager();
        if ($this->tablePlay == null) {
            $this->recupTable();
            $this->addFormTable($request);
        } else {
            $this->recupTablePleine();
        }
    }

    public function recupTable() {
        $tables = array();
        foreach ($this->nameTable as $value) {
            $query = $this->em->createQuery(
                            "SELECT t
                                FROM AfpaPokerGameBundle:TablePoker t
                                WHERE t.name = :name
                                AND t.nbPosition > t.nbInscrit"
                    )->setParameter(':name', $value);
            $aPendingTables = $query->getResult();
            $nb = count($aPendingTables) == 0 ? $this->nbTable : $this->nbTable - count($aPendingTables);
            if ($nb > 0) {
                $tables = array_merge($tables, $this->createTable($nb, $aPendingTables, $value));
            } else {
                $tables = array_merge($tables, $aPendingTables);
            }
        }

        $this->aPendingTables = $tables;
    }

    public function createTable($nb, $aPendingTables, $nameTable) {
        for ($i = 0; $i < $nb; $i++) {
            $oTablePoker = new TablePoker();
            $oTablePoker->setName($nameTable);
            $oTablePoker->setNbPosition($this->tableInit[$nameTable]['nbPos']);
            $oTablePoker->setFactor($this->tableInit[$nameTable]['factor']);
            $oTablePoker->setTimeLevel($this->tableInit[$nameTable]['timeLevel']);
            $oTablePoker->setInitialBet($this->tableInit[$nameTable]['initialBet']);
            $oTablePoker->setStackTable($this->tableInit[$nameTable]['stack']);
            $oTablePoker->setBuyIn($this->tableInit[$nameTable]['buyIn']);
            $oTablePoker->setNbInscrit($this->tableInit[$nameTable]['nbInscrit']);
            $this->em->persist($oTablePoker);
            $this->em->flush();
            $aPendingTables[] = $oTablePoker;
        }
        return $aPendingTables;
    }

    public function addFormTable(Request $request) {
        foreach ($this->aPendingTables as $key => $value) {

            $inscrit = $this->PlayerInscrit($value->getId());
            if ($value->getNbInscrit() < $value->getNbPosition()) {
                $form = $this->createFormBuilder()
                        ->add('id', HiddenType::class, array('data' => $value->getId()))
                        ->add('action', HiddenType::class, array('data' => $inscrit == false ? 'in' : 'out'))
                        ->add('inscription', SubmitType::class, array('label' => $inscrit == false ? 'S\'inscrire' : 'Se désinscrire'))
                        ->getForm();

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid() && $form->getNormData()['id'] == $value->getId()) {
                    $this->inscriptionTable = array('action' => $form->getNormData()['action'], 'idTable' => $form->getNormData()['id'], 'arrayTable' => $key);
                }

                $this->aPendingTables[$key] = array('form' => $form->createView(), 'table' => $value,);
            } else {
                $this->aPendingTables[$key] = array('table' => $value);
            }
        }
    }

    public function PlayerInscrit($value) {
        $playerTable = $this->em->getRepository('AfpaPokerGameBundle:Player')->findByTablePoker($value);
        $userExist = false;
        foreach ($playerTable as $player) {
            if ($player->getUser()->getId() == $this->session->get('user')->getId()) {
                $userExist = true;
                break;
            }
        }
        return $userExist;
    }

    public function recupTablePleine() {
        $tables = array();
        foreach ($this->nameTable as $value) {
            $query = $this->em->createQuery(
                            "SELECT t
                                FROM AfpaPokerGameBundle:TablePoker t
                                WHERE t.name = :name
                                AND t.nbPosition = t.nbInscrit"
                    )->setParameter(':name', $value);
            $aPendingTables = $query->getResult();

            $tables = array_merge($tables, $aPendingTables);
        }
        $this->aPendingTables = $tables;
    }

    public function miseAJourPlayerCredit(Request $request, $id, $credit) {
        $user = $this->em->getRepository('AfpaPokerGameBundle:User')->findOneById($id);
        $newMonnaie = $user->getVirtualMoney() + $credit;
        $user->setVirtualMoney($newMonnaie);
        $this->em->flush();
        $userSession = $request->getSession()->get('user');
        $userSession->setVirtualMoney($newMonnaie);
    }

    /**
     *
     * verifier dans recupTable si la requete sql ne peut pas etre fait autrement
     * voir dans createTable si possible de faire une hydratation
     * voir dans addFormTable pour reduire
     * voir redondence avec recuptablepleine et recuptable
     * voir dans prepare table pour alleger le code
     *
     *
     */

    /**
     * @Route("/openTableRefresh", name="_open_table_refresh")
     */
    public function openTableRefreshAction(Request $request) {
        $array = array();

        $this->session = $request->getSession();
        $this->em = $this->getDoctrine()->getManager();
        $arrayPartie = $this->session->get('partie') ? $this->session->get('partie') : array();
        foreach ($arrayPartie as $key => $value) {
            $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->findOneById($key);

            if ($table->getNbInscrit() == $table->getNbPosition()) {
                foreach ($this->session->get('ouverture') as $ouverture) {
                    if ($ouverture['table'] == $key && $ouverture['allReady'] == FALSE && $ouverture['permission'] == FALSE) {
                        $array[] = array(
                            'table' => $key,
                            'permission' => true,
                            'allReady' => false);
                    } else {
                        $array[] = array(
                            'table' => $key,
                            'permission' => false,
                            'allReady' => true);
                    }
                }
            } else {
                $array[] = array(
                    'table' => $key,
                    'permission' => false,
                    'allReady' => false);
            }
        }
        return $this->render('AfpaPokerGameBundle:TablePoker:open_table_refresh.html.twig', array(
                    'popUp' => $array,
        ));
    }

    /**
     * @Route("/play/{idTable}", name="_play")
     */
    public function playAction($idTable, Request $request) {
        $this->em = $this->getDoctrine()->getManager();
        $this->session = $request->getSession();
//test user connecté
        $oSession = $request->getSession();
        if (!$oSession->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }

//User
        $user = $oSession->get('user')->getId();

//TablePoker en cours
        $repo = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:TablePoker');
        $oTablePoker = $repo->find($idTable);

//PlayerList
        $repoP = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:Player');
        $aListPlayer = $repoP->findBy(array('tablePoker' => $idTable));



//$nbPlayer = count($aListPlayer);
        $nbPlayer = $oTablePoker->getNbPosition();

//test si partie en cours
        $partieEnCours = ($oTablePoker->getNbPosition() == $oTablePoker->getNbInscrit());
        if ($partieEnCours) {
//test si user est player
            $verif = $this->PlayerInscrit($idTable);
            if ($verif) {
                $oCard = new Card();
                $aCards = $oCard->getDeck();
                $oTablePoker->setPackOfCards(serialize($aCards));
                $oTablePoker->setTimeStart(new \dateTime('now'));
                dump($oTablePoker);

                $this->em->persist($oTablePoker);
                $this->em->flush();

//Tableau Avatars
                $repo2 = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:User');
                for ($i = 0; $i < $nbPlayer; $i++) {
                    $aAvatar[] = $aListPlayer[$i]->getUser()->getAvatar();
                    $aPseudo[] = $aListPlayer[$i]->getUser()->getPseudo();
                }




                return $this->render('AfpaPokerGameBundle:TablePoker:play.html.twig', array(
                            'listPlayer' => $aListPlayer,
                            'avatar' => $aAvatar,
                            'pseudo' => $aPseudo,
                            'idTable' => $idTable,
                ));
            } else {
//sinon on rendra just_view

                return $this->render('AfpaPokerGameBundle:TablePoker:just_view.html.twig', array(
                            'idTable' => $idTable
                ));
            }
        }
    }

    /**
     * @Route("/justView/{idTable}", name="_just_view")
     */
    public function justViewAction($idTable, Request $request) {
// seulement visualisation de la table de jeu
        return $this->render('AfpaPokerGameBundle:TablePoker:just_view.html.twig', array(
                    'idTable' => $idTable
        ));
    }

    /**
     * @Route("/view/{idTable}", name="_game_view")
     */
    public function gameViewAction($idTable, Request $request) {
        $this->em = $this->getDoctrine()->getManager();
        $this->session = $request->getSession();
//test user connecté
        $oSession = $request->getSession();
        if (!$oSession->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }

//TablePoker en cours
        $repo = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:TablePoker');
        $oTablePoker = $repo->find($idTable);
        $initialBet = $oTablePoker->getInitialBet();



//Player
        $repoP = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:Player');
        $aListPlayer = $repoP->findBy(array('tablePoker' => $idTable));
        $playerUser = $repoP->findOneBy(array('tablePoker' => $idTable, 'user' => $oSession->get('user')->getId()))->getId();
        $bTurnPlay = !$repoP->findOneBy(array('tablePoker' => $idTable, 'user' => $oSession->get('user')->getId()))->getTurn();



//En cours jetons
        $enCours = $repoP->findOneBy(array('tablePoker' => $idTable, 'user' => $oSession->get('user')->getId()))->getEncoursJetons();


//$nbPlayer = count($aListPlayer);
        $nbPlayer = $oTablePoker->getNbPosition();





        //Création du formulaire de betting

        $oForm = $this->createFormBuilder()
                ->add('bet', IntegerType::class, array('data' => $initialBet))
                ->add('bet2', RangeType::class, array('attr' => array('value' => $initialBet, 'min' => $initialBet, 'max' => $enCours, 'step' => $initialBet)))
                ->add('fold', SubmitType::class, array('label' => 'fold', 'disabled' => $bTurnPlay))
                ->add('check', SubmitType::class, array('label' => 'check', 'disabled' => $bTurnPlay))
                ->add('raise', SubmitType::class, array('label' => 'raise', 'disabled' => $bTurnPlay))
                ->getForm();



        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    'idTable' => $idTable,
                    'tablePoker' => $oTablePoker,
                    'listPlayer' => $aListPlayer,
                    'formBet' => $oForm->createView(),
                    'user' => $oSession->get('user')->getId(),
                    'PlayerUser' => $playerUser,
        ));
    }

    /**
     * @Route("/check/{idTable}", name="_check_view")
     */
    public function checkAction($idTable, Request $request) {
        $oSession = $request->getSession();
        $this->em = $this->getDoctrine()->getManager();
        $this->session = $request->getSession();

        $repoP = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:Player');


        $enCours = $repoP->findOneBy(array('tablePoker' => $idTable, 'user' => $oSession->get('user')->getId()))->getEncoursJetons();


        //TablePoker en cours
        $repo = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:TablePoker');
        $oTablePoker = $repo->find($idTable);

        //
    }

}
