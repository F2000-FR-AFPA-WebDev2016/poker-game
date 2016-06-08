<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Afpa\PokerGameBundle\Entity\User;
use Afpa\PokerGameBundle\Entity\TablePoker;
use Afpa\PokerGameBundle\Entity\Player;
use Afpa\PokerGameBundle\Models\PokerHand;
use Afpa\PokerGameBundle\Models\Card;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\HttpFoundation\Session\Session;

class TablePokerController extends Controller {

    private $session;
    private $tablePlay;
    private $em;
    private $aPendingTables;
    private $inscriptionTable;
    private $nbTable = 2;
    private $cardPlayers = array('cardOne', 'CardTwo');
    private $nameTable = array('Heads up', 'Heads up turbo');
    private $tableInit = array(
        'Heads up' => array('nbPos' => 2, 'factor' => 2, 'timeLevel' => 2, 'initialBet' => 15, 'stack' => 5000, 'buyIn' => 5, 'nbInscrit' => 0),
        'Heads up turbo' => array('nbPos' => 2, 'factor' => 2, 'timeLevel' => 1, 'initialBet' => 100, 'stack' => 1500, 'buyIn' => 10, 'nbInscrit' => 0));

    public function init(Request $request) {
        $this->em = $this->getDoctrine()->getManager();
        $this->session = $request->getSession();
        if (!$this->session->get('user') instanceof User) {
            return false;
        }
        return true;
    }

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

        $arraySessionPartie = array();
        $array = array();
        $this->session = $request->getSession();
        $this->em = $this->getDoctrine()->getManager();

        if ($this->session->get('user') instanceof User) {
            $session = new Session();
            $partiesUser = $this->em->getRepository('AfpaPokerGameBundle:Player')->findByUser($this->session->get('user')->getId());
            if (count($partiesUser) > 0) {
                foreach ($partiesUser as $valueUser) {
                    $arraySessionPartie[$valueUser->getTablePoker()->getId()] = $valueUser;
                }
            }
            $session->set('partie', $arraySessionPartie);


            foreach ($partiesUser as $key => $value) {


                $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->findOneById($value->getTablePoker()->getId());
                if ($table->getNbInscrit() == $table->getNbPosition() && count($this->session->get('ouverture')) > 0) {
                    if (isset($this->session->get('ouverture')[$value->getTablePoker()->getId()]) && $this->session->get('ouverture')[$value->getTablePoker()->getId()]['allReady'] == FALSE && $this->session->get('ouverture')[$value->getTablePoker()->getId()]['permission'] == FALSE) {
                        $array[$value->getTablePoker()->getId()] = array(
                            'table' => $value->getTablePoker()->getId(),
                            'permission' => true,
                            'allReady' => false);
                    } elseif (isset($this->session->get('ouverture')[$value->getTablePoker()->getId()])) {
                        $array[$value->getTablePoker()->getId()] = array(
                            'table' => $value->getTablePoker()->getId(),
                            'permission' => false,
                            'allReady' => true);
                    } else {
                        $array[$value->getTablePoker()->getId()] = array(
                            'table' => $value->getTablePoker()->getId(),
                            'permission' => true,
                            'allReady' => false);
                    }
                } else {
                    $array[$value->getTablePoker()->getId()] = array(
                        'table' => $value->getTablePoker()->getId(),
                        'permission' => false,
                        'allReady' => false);
                }
            }
            $session->set('ouverture', $array);
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
                if ($oTablePoker->getPackOfCards() == null) {
                    $oCard = new Card();
                    $aCards = $oCard->getDeck();
                    $oTablePoker->setPackOfCards(serialize($aCards));


                    $this->em->persist($oTablePoker);
                    $this->em->flush();
                }


//Tableau Avatars

                for ($i = 0; $i < $nbPlayer; $i++) {

                    $aPseudo[] = $aListPlayer[$i]->getUser()->getPseudo();
                    $aAvatar[] = $aListPlayer[$i]->getUser()->getAvatar() == null ? 'avatar_null.jpg' : $aListPlayer[$i]->getUser()->getAvatar();
                }

                return $this->render('AfpaPokerGameBundle:TablePoker:play.html.twig', array(
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
        } else {
            return $this->redirect($this->generateUrl('_list_partie'));
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

        $array = $this->initialisePlay($idTable, $request, $this->startPlay($idTable) == false ? true : false);

        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
        ));
    }

    public function startPlay($idTable, $init = null) {
        if ($init != false) {
            $oTablePoker = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
            $date = time();
            $oTablePoker->setTimeStart($date);
            $this->em->persist($oTablePoker);
            $this->em->flush();
        } else {
            $oTablePoker = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
            $date = $oTablePoker->getTimeStart() != null ? $oTablePoker->getTimeStart() : false;
        }
        return $date;
    }

    public function initialisePlay($idTable, Request $request, $init = null) {
        $array = array();
        if ($init != false) {
            $array['initPartie']['dateDepart'] = $this->startPlay($idTable, $init);
        } elseif ($this->startPlay($idTable) > time() - 10) {

            $array['initPartie']['dateDepart'] = $this->startPlay($idTable, $init);
        } elseif ($this->startPlay($idTable) > time() - 16) {
            $array = $this->dealAction($idTable, $request, true);
        } else {
            $array = $this->newMainAction($idTable, $request, true);
        }

        return $array;
    }

    public function tirageDeal($idTable) {
//TablePoker en cours
        $oTablePoker = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
//PlayerList
        $aListPlayer = $this->em->getRepository('AfpaPokerGameBundle:Player')->findByTablePoker($idTable);
        $nbPlayer = $oTablePoker->getNbPosition();
        $player = array();
        $dealer = '';
        $verifDeal = '';
        $verifNew = '';
        $cardsColor = Card::recupValueCard('color');
        $cardsValue = Card::recupValueCard('value');
        if ($aListPlayer[0]->getTirageDeal() == null) {
            for ($i = 0; $i < $nbPlayer; $i++) {
                $carte = unserialize($oTablePoker->getPackOfCards());
                if ($dealer == '') {
                    $dealer = $carte[$i];
                } else {
                    $valCardDeal = substr($dealer, 0, 1);
                    $colCardDeal = substr($dealer, 1, 1);
                    $valCardNew = substr($carte[$i], 0, 1);
                    $colCardNew = substr($carte[$i], 1, 1);
                    foreach ($cardsValue as $k => $v) {
                        if ($valCardDeal == $v) {
                            $verifDeal = $k;
                        }
                        if ($valCardNew == $v) {
                            $verifNew = $k;
                        }
                    }
                    if ($verifDeal == $verifNew) {
                        foreach ($cardsColor as $kcol => $vcol) {
                            if ($colCardDeal == $vcol) {
                                $verifDeal = $kcol;
                            }
                            if ($colCardNew == $vcol) {
                                $verifNew = $kcol;
                            }
                        }
                    }
                    $dealer = $verifDeal < $verifNew ? $dealer : $carte[$i];
                }
                $player[] = array(
                    'id' => $aListPlayer[$i]->getUser()->getId(),
                    'pseudo' => $aListPlayer[$i]->getUser()->getPseudo(),
                    'carteDeal' => $carte[$i],
                    'dealer' => 'false'
                );
            }
            foreach ($player as $key => $value) {
                if ($value['carteDeal'] == $dealer) {

                    $player[$key]['dealer'] = 'true';
                    $playerDealer = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('user' => $player[$key]['id'], 'tablePoker' => $idTable));
                    $playerDealer[0]->setDealer(true);
                    $this->em->persist($playerDealer[0]);
                }
                $playerTirage = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('user' => $player[$key]['id'], 'tablePoker' => $idTable));
                $playerTirage[0]->setTirageDeal($value['carteDeal']);
                $this->em->persist($playerTirage[0]);
            }
        } else {
            for ($i = 0; $i < $nbPlayer; $i++) {
                $carte = unserialize($oTablePoker->getPackOfCards());

                $player[] = array(
                    'id' => $aListPlayer[$i]->getUser()->getId(),
                    'pseudo' => $aListPlayer[$i]->getUser()->getPseudo(),
                    'carteDeal' => $aListPlayer[$i]->getTirageDeal(),
                    'dealer' => $aListPlayer[$i]->getDealer() == null ? 'false' : 'true'
                );
            }
        }
        $this->em->flush();
        return $player;
    }

    /**
     * @Route("/deal/{idTable}", name="_deal")
     */
    public function dealAction($idTable, Request $request, $init = null) {
        $verif = $this->init($request);
        if ($verif == FALSE) {
            return $this->redirect($this->generateUrl('_home'));
        }

        $players = $this->tirageDeal($idTable);


        if ($init == true) {
            $tab['dealCards'] = $players;
            return $tab;
        }
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    'dealCards' => $players
        ));
    }

    /**
     * @Route("/newMain/{idTable}", name="_new_main")
     */
    public function newMainAction($idTable, Request $request, $init = null) {
        $verif = $this->init($request);
        if ($verif == FALSE) {
            return $this->redirect($this->generateUrl('_home'));
        }

        $newPack = new Card();
        $packOfCard = $newPack->getDeck();
        $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
        $players = $this->em->getRepository('AfpaPokerGameBundle:Player')->findByTablePoker($idTable);
        if ($players[0]->getCardOne() == null) {
            foreach ($players as $player) {
                $player->setEncoursJetons($table->getStackTable());
                $player->setCardOne(array_pop($packOfCard));
                array_pop($packOfCard);
                $player->setCardTwo(array_pop($packOfCard));
                array_pop($packOfCard);
                $this->em->persist($player);
                if ($player->getDealer() == 1) {
                    $positionDealer = $player->getPosition();
                }
            }
            $table->setOC1(array_pop($packOfCard));
            array_pop($packOfCard);
            $table->setOC2(array_pop($packOfCard));
            array_pop($packOfCard);
            $table->setOC3(array_pop($packOfCard));
            array_pop($packOfCard);
            $table->setOC4(array_pop($packOfCard));
            array_pop($packOfCard);
            $table->setOC5(array_pop($packOfCard));
            array_pop($packOfCard);
            $table->setPackOfCards(serialize($packOfCard));
            $this->em->persist($table);

            $aQuiDeJouer = $this->trouvePlace($table->getNbINscrit(), $positionDealer, 3);
            $insertAquiDeJouer = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'position' => $aQuiDeJouer));
            $insertAquiDeJouer[0]->setTurn(true);

            $petiteBlind = $this->trouvePlace($table->getNbINscrit(), $positionDealer, 1);
            $insertPetiteBlind = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'position' => $petiteBlind));
            $insertPetiteBlind[0]->setMiseJetons($table->getInitialBet());
            $insertPetiteBlind[0]->setEncoursJetons($table->getStackTable() - $table->getInitialBet());

            $grosseBlind = $this->trouvePlace($table->getNbINscrit(), $positionDealer, 2);
            $insertGrosseBlind = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'position' => $grosseBlind));
            $insertGrosseBlind[0]->setMiseJetons($table->getInitialBet() * 2);
            $insertGrosseBlind[0]->setEncoursJetons($table->getStackTable() - $table->getInitialBet() * 2);




            $this->em->persist($insertAquiDeJouer[0]);
            $this->em->persist($insertPetiteBlind[0]);
            $this->em->persist($insertGrosseBlind[0]);
        }

        $user = $this->session->get('user')->getId();
        $array = array();
        foreach ($players as $key => $value) {
            if ($value->getUser()->getId() == $user) {
                $array[$key]['cardOne'] = $value->getCardOne();
                $array[$key]['cardTwo'] = $value->getCardTwo();
                if ($value->getTurn() != null) {
                    $array[$key]['turn'] = 'true';
                }
            } else {
                $array[$key]['cardOne'] = 'VERSO';
                $array[$key]['cardTwo'] = 'VERSO';
            }
            $array[$key]['encoursJetons'] = $value->getEncoursJetons();
            $array[$key]['miseJetons'] = $value->getMiseJetons();
            $array[$key]['dealer'] = $value->getDealer() == true ? 'true' : 'false';

            $array[$key]['pot'] = $table->getPot();
            $array[$key]['c1'] = 'VERSO';
            $array[$key]['c2'] = 'VERSO';
            $array[$key]['c3'] = 'VERSO';
            $array[$key]['c4'] = 'VERSO';
            $array[$key]['c5'] = 'VERSO';
        }


        if ($init == true) {
            $tab['newMain'] = $array;
            return $tab;
        }

        $this->em->flush();
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    'newMain' => $array
        ));
    }

    /**
     * @Route("/check/{idTable}", name="check")
     */
    public function checkAction($idTable, Request $request) {
        dump($idTable);
        die();
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig');
    }

    /**
     * @Route("/fold/{idTable}", name="fold")
     */
    public function foldAction($idTable, Request $request) {
        dump($idTable);
        die();
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig');
    }

    /**
     * @Route("/bet/{idTable}/{montant}", name="bet")
     */
    public function betAction($idTable, Request $request, $montant) {
        dump($montant);
        die();
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig');
    }

    /**
     * @Route("/raise/{idTable}/{montant}", name="raise")
     */
    public function raiseAction($idTable, Request $request, $montant) {
        dump($montant);
        die();
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig');
    }

    public function trouvePlace($nbInscrit, $positionDealer, $var) {

        $placeRecherche = $positionDealer + $var;
        if ($placeRecherche > $nbInscrit) {
            $placeRecherche = $placeRecherche % $nbInscrit;
            if ($placeRecherche == 0) {
                $placeRecherche = $nbInscrit;
            }
        }

        return $placeRecherche;
    }

    /**
     * @Route("test", name="test")
     */
    public function test() {

//
        $oCard = new Card();
        $toto = $oCard->getDeck();
//        for ($i = 1; $i <= 7; $i++) {
//            $aPokerHand[] = array_pop($toto);
//        }
        $aPokerHand = array('QD', '6C', '2C', '4C', '4D', '3C', '5C');

        $oPokerHand = new PokerHand($aPokerHand);
        $oPokerHand->getForceHand($aPokerHand);


        dump($aPokerHand);
        dump($oPokerHand->getTypeHand($aPokerHand));
        dump($oPokerHand->getForceHand($aPokerHand));
        die;

        return $this->render('AfpaPokerGameBundle:TablePoker:test.html.twig', array(
        ));
    }

    /**
     * @Route("/view2/{idTable}", name="_game_view2")
     */
    public function gameView2Action($idTable, Request $request) {



//TablePoker en cours
        $repo = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:TablePoker');
        $oTablePoker = $repo->find($idTable);
        $initialBet = $oTablePoker->getInitialBet();
        $stack = $oTablePoker->getStackTable();

        $packOfCards = unserialize($oTablePoker->getPackOfCards());



//Player
        $repoP = $this->getDoctrine()->getRepository('AfpaPokerGameBundle:Player');
        $aListPlayer = $repoP->findByTablePoker($idTable);
        foreach ($aListPlayer as $value) {
            $value->setCardOne = array_pop($packOfCards);
            $value->setCardTwo = array_pop($packOfCards);
            $value->setEncoursJetons = $stack;
        }



        $playerUser = $repoP->findOneBy(array('tablePoker' => $idTable, 'user' => $oSession->get('user')->getId()))->getId();
        $bTurnPlay = !$repoP->findOneBy(array('tablePoker' => $idTable, 'user' => $oSession->get('user')->getId()))->getTurn();
        $bTurnPlay = false;



//En cours jetons
        $enCours = $repoP->findOneBy(array('tablePoker' => $idTable, 'user' => $oSession->get('user')->getId()))->getEncoursJetons();






        //Création du formulaire de betting

        $oForm = $this->createFormBuilder()
                ->add('bet', IntegerType::class, array('data' => $initialBet))
                ->add('check', SubmitType::class, array('label' => 'check', 'disabled' => $bTurnPlay))
                ->getForm();

        $oForm->handleRequest($request);

        if ($oForm->isSubmitted() && $oForm->isValid()) {
            dump($oForm);
            die;
        }
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    'idTable' => $idTable,
                    'tablePoker' => $oTablePoker,
                    'listPlayer' => $aListPlayer,
                    'formBet' => $oForm->createView(),
                    'user' => $oSession->get('user')->getId(),
                    'PlayerUser' => $playerUser,
                    'posts' => $_POST != null ? $_POST : array()
        ));
    }

}
