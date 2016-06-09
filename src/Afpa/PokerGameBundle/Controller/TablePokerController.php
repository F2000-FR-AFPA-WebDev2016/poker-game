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
                            'idUser' => $user
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
        $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
        if($table->getTour() == 4){
            $array = $this->affichageGagnant($idTable);
            return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
            ));
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
                $playerTirage[0]->setPlayMain(true);
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
        if($table->getTour() == 4){
            $array = $this->affichageGagnant($idTable);
            return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
            ));
        }
        $players = $this->em->getRepository('AfpaPokerGameBundle:Player')->findByTablePoker($idTable);
        if ($players[0]->getCardOne() == null) {
            foreach ($players as $player) {
                $newStack = $player->getEncoursJetons() === null ?  $table->getStackTable() : $player->getEncoursJetons();
                $player->setEncoursJetons($newStack);
                $player->setCardOne(array_pop($packOfCard));
                array_pop($packOfCard);
                $player->setCardTwo(array_pop($packOfCard));
                array_pop($packOfCard);

                if($player->getDealer() == 1){
                    $positionDealer = $player->getPosition();
                }
                $this->em->persist($player);
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

            $aQuiDeJouer = $this->trouvePlace($table->getNbINscrit(), $positionDealer, 3);
            $insertAquiDeJouer = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'position' => $aQuiDeJouer));
            $insertAquiDeJouer[0]->setTurn(true);

            $petiteBlind = $this->trouvePlace($table->getNbINscrit(), $positionDealer, 1);

            $insertPetiteBlind = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array( 'tablePoker' => $idTable,'position' => $petiteBlind));
            
            $stackSB = $insertPetiteBlind[0]->getEncoursJetons() == null ? $table->getStackTable() - $table->getInitialBet(): $insertPetiteBlind[0]->getEncoursJetons()- $table->getInitialBet();
            if($stackSB < 1){
                $negatif = $stackSB;
                $stackSB = 0;
            }else{
                $negatif = 0;
            }
            $insertPetiteBlind[0]->setMiseJetons($table->getInitialBet() - $negatif);
            $insertPetiteBlind[0]->setEncoursJetons($stackSB );
            
            
            $grosseBlind = $this->trouvePlace($table->getNbINscrit(), $positionDealer, 2);
            $insertGrosseBlind = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array( 'tablePoker' => $idTable,'position' => $grosseBlind));
            
            $stackBB = $insertGrosseBlind[0]->getEncoursJetons() == null ? $table->getStackTable() - $table->getInitialBet() * 2: $insertGrosseBlind[0]->getEncoursJetons() - $table->getInitialBet() * 2;
            if($stackBB < 1){
                $negatif = $stackBB;
                $stackBB = 0;
            }else{
                $negatif = 0;
            }
            $insertGrosseBlind[0]->setMiseJetons($table->getInitialBet() * 2 - $negatif);
            $insertGrosseBlind[0]->setEncoursJetons($stackBB );
            $insertGrosseBlind[0]->setLastPlayer(1);
            
            
            
            $table->setPot($table->getInitialBet() * 3);
            $table->setTour(0);
            
            
            $this->em->persist($table);
            $this->em->persist($insertAquiDeJouer[0]);
            $this->em->persist($insertPetiteBlind[0]);
            $this->em->persist($insertGrosseBlind[0]);
            $this->em->flush();
             

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
            $array[$key]['c1'] = $table->getTour() == 0 ? 'VERSO' : $table->getOC1();
            $array[$key]['c2'] = $table->getTour() == 0 ? 'VERSO' : $table->getOC2();
            $array[$key]['c3'] = $table->getTour() == 0 ? 'VERSO' : $table->getOC3();
            $array[$key]['c4'] = $table->getTour() <= 1 ? 'VERSO' : $table->getOC4();
            $array[$key]['c5'] = $table->getTour() <= 2 ? 'VERSO' : $table->getOC5();
            $array[$key]['idUser'] = $value->getUser()->getId();
        }


        if ($init == true) {
            $tab['newMain'] = $array;
            return $tab;
        }
        
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    'newMain' => $array
        ));
    }
    

    /**
     * @Route("/fold/{idTable}", name="fold")
     */
    public function foldAction($idTable, Request $request) {

        $verif = $this->init($request);
        if($verif == FALSE){ 
            return $this->redirect($this->generateUrl('_home'));
        }
        $user = $this->session->get('user')->getId();
        
        $players = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1));
        $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
        if($table->getTour() > 3){
            $array = $this->affichageGagnant($idTable);
            return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
            ));
        }
        if(count($players) == 2){
            
                foreach($players as $index => $player){
                    if($player->getUser()->getId() != $user){
                        $player->setEncoursJetons($player->getEncoursJetons() + $table->getPot());
                        $this->em->persist($player);
                    }
                }
                $playersAll = $this->em->getRepository('AfpaPokerGameBundle:Player')->findByTablePoker($idTable);
                foreach($playersAll as $index => $player){
                    if($player->getDealer() == 1){
                        $positionDealer = $player->getPosition();
                    }
                    $player->setDealer(null);
                    $player->setPlayMain(true);
                    $player->setLastPlayer(null);
                    $player->setTurn(null);
                    $player->setAllIn(null);
                    $player->setMiseJetons(null);
                    $player->setCardOne(null);
                    $player->setCardTwo(null);
                    $this->em->persist($player);
                }
                $table->setPot(null);

                $newPlaceDealer = $this->trouvePlace($table->getNbPosition(), $positionDealer, 1);
                $NewDealer = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'position' => $newPlaceDealer));
                $NewDealer[0]->setDealer(1);
                $this->em->persist($NewDealer[0]);
            
                
            
            
        }else{
            $newLastPlayer = '';
            foreach($players as $index => $player){
                if($player->getUser()->getId() == $user){
                    $player->setPlayMain(false);
                    $player->setTurn(null);
                    $player->setAllIn(null);
                    $player->setMiseJetons(null);
                    $player->setCardOne(null);
                    $player->setCardTwo(null);
                    if($player->getLastPlayer() == 1){
                        $player->setLastPlayer(null);
                        $newLastPlayer = $player->getPosition();
                        $table->setTour($table->getTour() + 1);
                    }
                    $playMain = $player->getPosition();
                    $this->em->persist($player);
                    
                    break;
                }
            }
            if($newLastPlayer != null){
                
                for($i = 1; $i <= $table->getNbPosition() ; $i++){
                    $newPlaceLastPlayer = $this->trouveLastPlayer($table->getNbPosition(), $newLastPlayer, $i );
                    $verifLast = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'position' => $newPlaceLastPlayer));
                    if(count($verifLast) > 0){
                        break;
                    }
                }
                $verifLast[0]->setLastPlayer(true);
                $this->em->persist($verifLast[0]);
            }
            for($i = 1; $i <= $table->getNbPosition() ; $i++){
                $aQuiDeJouer = $this->trouvePlace($table->getNbPosition(), $playMain, $i );
                $verifAQui = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'position' => $aQuiDeJouer));
                 if(count($verifAQui) > 0){
                    break;
                }
            }
            $verifAQui[0]->setTurn(true);
            $this->em->persist($verifAQui[0]);
            $this->em->persist($table);
                
        }
        
        $this->em->flush();
        if($table->getTour() > 3){
            $array = $this->affichageGagnant($idTable);
            return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                        key($array) => current($array)
                ));
        }
        $array = $this->newMainAction($idTable, $request, true);
        
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
        ));
    }

    /**
     * @Route("/bet/{idTable}/{montant}", name="bet")
     */
    public function betAction($idTable, Request $request, $montant) {

        $verif = $this->init($request);
        if($verif == FALSE){ 
            return $this->redirect($this->generateUrl('_home'));
        }
        $user = $this->session->get('user')->getId();
        
        $lastPlayers = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'user' => $user));
        $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
        if($table->getTour() > 3){
            $array = $this->affichageGagnant($idTable);
            return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
            ));
        }
        if($lastPlayers[0]->getLastPlayer() == null){
            $players = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1));
            foreach($players as $index => $player){
                if($player->getUser()->getId() == $user){
                    
                    $player->setMiseJetons($player->getMiseJetons() + $montant);
                    $player->setEncoursJetons($player->getEncoursJetons() - $montant);
                    $player->setTurn(false);
                    $playMain = $player->getPosition();
                    $table->setPot($table->getPot() + $montant);
                    $this->em->persist($player);
                    $this->em->persist($table);
                }
            }
            
            $aQuiDeJouer = $this->trouvePlace($table->getNbINscrit(), $playMain, 1);
            $insertAquiDeJouer = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array( 'tablePoker' => $idTable,'position' => $aQuiDeJouer));
            $insertAquiDeJouer[0]->setTurn(true);
            $this->em->persist($insertAquiDeJouer[0]);
            
        }else{
            $table->setTour($table->getTour() + 1);
            $table->setPot($table->getPot() + $montant);
            $playersAll = $this->em->getRepository('AfpaPokerGameBundle:Player')->findByTablePoker($idTable);
            foreach($playersAll as $index => $player){
                if($player->getPlayMain() == 1){
                    $player->setTurn(null);
                    $player->setMiseJetons(null);
                }
                if($player->getDealer() == 1){
                    $dealer = $player->getPosition() + 1;
                }
                if($player->getUser()->getId() == $user){
                    $nextJoueur = $player->getPosition();
                    $player->setEncoursJetons($player->getEncoursJetons() - $montant);
                }
                $this->em->persist($player);
            }
            
            for($i = 1; $i <= $table->getNbPosition() ; $i++){
                $aQuiDeJouer = $this->trouvePlace($table->getNbPosition(), $nextJoueur, $i );
                $verifAQui = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'position' => $aQuiDeJouer));
                 if(count($verifAQui) > 0){
                    break;
                }
            }
            for($i = 1; $i <= $table->getNbPosition() ; $i++){
                $newPlaceLastPlayer = $this->trouveLastPlayer($table->getNbPosition(), $dealer, $i );
                $verifLast = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'position' => $newPlaceLastPlayer));
                if(count($verifLast) > 0){
                    break;
                }
            }
            $verifLast[0]->setLastPlayer(true);
            $verifAQui[0]->setTurn(true);
            $this->em->persist($verifLast[0]);
            $this->em->persist($verifAQui[0]);
            $this->em->persist($table);
        }
        
        
        $this->em->flush();
        if($table->getTour() > 3){
            $array = $this->affichageGagnant($idTable);
            return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                        key($array) => current($array)
                ));
        }
        $array = $this->newMainAction($idTable, $request, true);
        
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
        ));
        
    }
    
    /**
     * @Route("/check/{idTable}", name="check")
     */
    public function checkAction($idTable, Request $request) {
        $verif = $this->init($request);
        if($verif == FALSE){ 
            return $this->redirect($this->generateUrl('_home'));
        }
        $user = $this->session->get('user')->getId();
        
        $players = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'user' => $user));
        $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
        if($table->getTour() > 3){
            $array = $this->affichageGagnant($idTable);
            return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
            ));
        }
        if($players[0]->getLastPlayer() == null){
            
            $players[0]->setTurn(0);
            $this->em->persist($players[0]);
            
            $position = $players[0]->getPosition();
            
            $aQuiDeJouer = $this->trouvePlace($table->getNbINscrit(), $position, 1);
            $insertAquiDeJouer = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array( 'tablePoker' => $idTable,'position' => $aQuiDeJouer));
            $insertAquiDeJouer[0]->setTurn(true);
            $this->em->persist($insertAquiDeJouer[0]);
            
        }else{
            
            $table->setTour($table->getTour() + 1);
            
            $playersAll = $this->em->getRepository('AfpaPokerGameBundle:Player')->findByTablePoker($idTable);
            foreach($playersAll as $index => $player){
                if($player->getPlayMain() == 1){
                    $player->setTurn(null);
                    $player->setMiseJetons(null);
                }
                if($player->getDealer() == 1){
                    $dealer = $player->getPosition() + 1;
                }
                if($player->getUser()->getId() == $user){
                    $nextJoueur = $player->getPosition();
                }
                $this->em->persist($player);
            }
        
            
            for($i = 1; $i <= $table->getNbPosition() ; $i++){
                $aQuiDeJouer = $this->trouvePlace($table->getNbPosition(), $nextJoueur, $i );
                $verifAQui = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'position' => $aQuiDeJouer));
                 if(count($verifAQui) > 0){
                    break;
                }
            }
            for($i = 1; $i <= $table->getNbPosition() ; $i++){
                $newPlaceLastPlayer = $this->trouveLastPlayer($table->getNbPosition(), $dealer, $i );
                $verifLast = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'position' => $newPlaceLastPlayer));
                if(count($verifLast) > 0){
                    break;
                }
            }
            $verifLast[0]->setLastPlayer(true);
            $verifAQui[0]->setTurn(true);
            $this->em->persist($verifLast[0]);
            $this->em->persist($verifAQui[0]);
            $this->em->persist($table);
        }
        
        
        
        $this->em->flush();
        if($table->getTour() > 3){
            $array = $this->affichageGagnant($idTable);
            return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                        key($array) => current($array)
                ));
        }
        $array = $this->newMainAction($idTable, $request, true);
        
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
        ));
    }
    
    /**
     * @Route("/raise/{idTable}/{montant}", name="raise")
     */
    public function raiseAction($idTable, Request $request, $montant) {

        
        $verif = $this->init($request);
        if($verif == FALSE){ 
            return $this->redirect($this->generateUrl('_home'));
        }
        $user = $this->session->get('user')->getId();
        
        $players = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1));
        $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
        foreach($players as $index => $player){
            if($player->getUser()->getId() == $user){
                $player->setMiseJetons($player->getMiseJetons() + $montant);
                $player->setEncoursJetons($player->getEncoursJetons() - $montant);
                $player->setTurn(false);
                $playMain = $player->getPosition();
                $table->setPot($table->getPot() + $montant);
                $this->em->persist($player);
                $this->em->persist($table);
            }
        }
            
            for($i = 1; $i <= $table->getNbPosition() ; $i++){
                $aQuiDeJouer = $this->trouvePlace($table->getNbPosition(), $playMain, $i );
                $verifAQui = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'position' => $aQuiDeJouer));
                 if(count($verifAQui) > 0){
                    break;
                }
            }
            for($i = 1; $i <= $table->getNbPosition() ; $i++){
                $newPlaceLastPlayer = $this->trouveLastPlayer($table->getNbPosition(), $playMain, $i );
                $verifLast = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1, 'position' => $newPlaceLastPlayer));
                if(count($verifLast) > 0){
                    break;
                }
            }
            $verifLast[0]->setLastPlayer(true);
            $verifAQui[0]->setTurn(true);
            $this->em->persist($verifLast[0]);
            $this->em->persist($verifAQui[0]);
            $this->em->persist($table);
        
        
        
        
        $this->em->flush();
        $array = $this->newMainAction($idTable, $request, true);
        
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                    key($array) => current($array)
        ));
        
        
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

    
    public function trouveLastPlayer($nbInscrit, $positionLast, $var){
        
        $placeRecherche = $positionLast - $var;
        
        if($placeRecherche == 0){
            $placeRecherche = $nbInscrit;
            
        }
        return $placeRecherche;
    }
    
    public function affichageGagnant($idTable){
        
        $user = $this->session->get('user')->getId();
        $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
        $players = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array( 'tablePoker' => $idTable, 'playMain' => 1));
        $array = array();
        
        foreach ($players as $key => $value) {
            $aPokerHand = array();
            
            
            $array[$key]['pseudo'] = $value->getUser()->getPseudo();
            $array[$key]['cardOne'] = $aPokerHand[] =  $value->getCardOne();
            $array[$key]['cardTwo'] = $aPokerHand[] = $value->getCardTwo();
            $array[$key]['encoursJetons'] = $value->getEncoursJetons();
            $array[$key]['miseJetons'] = $value->getMiseJetons();
            $array[$key]['dealer'] = $value->getDealer() == true ? 'true' : 'false';
            
            $array[$key]['pot'] = $table->getPot();
            $array[$key]['c1'] = $aPokerHand[] = $table->getOC1();
            $array[$key]['c2'] = $aPokerHand[] = $table->getOC2();
            $array[$key]['c3'] = $aPokerHand[] = $table->getOC3();
            $array[$key]['c4'] = $aPokerHand[] = $table->getOC4();
            $array[$key]['c5'] = $aPokerHand[] = $table->getOC5();
            $oPokerHand = new PokerHand($aPokerHand);
            
            $array[$key]['resultMain'] = $oPokerHand->getTypeHand($aPokerHand);
            $array[$key]['main'] = $oPokerHand->getForceHand($aPokerHand);
            
            
        }
        $mainGagnante = '';
        $egalite = '';
        foreach($array as $k => $v){
            if($mainGagnante == null){
                $mainGagnante = array('index' => $k, 'main' => $v['main']);
            }else{
                $verif = $this->CompareMain($mainGagnante['main'], $v['main']);
                if($verif == 'egalite'){
                    if($egalite == null){
                        $egalite[$k-1] = $array[$k-1]['pseudo'] ;
                    }
                    $egalite[$k] = $v['pseudo'] ;
                }elseif($verif == '2'){
                    $mainGagnante = array('index' => $k, 'main' => $v['main']);
                    $egalite = '';
                }
            }
        }
        if($egalite == ''){
            $array[$mainGagnante['index']]['gagnantMain'] = true;
        }else{
            $array[0]['egaliteMain'] = $egalite;
        }
        $tab['gagnant'] = $array;
        
        return $tab;
    }
    
    public function CompareMain($main1, $main2, $i = null){
        if($i == null){ $i = 0; }
        $tabMain1 = explode('-', $main1);
        $tabMain2 = explode('-', $main2);
        if(count($tabMain1) == $i){
            return 'egalite';
        }elseif($tabMain1[$i] > $tabMain2[$i]){
            return '1';
        }elseif($tabMain1[$i] < $tabMain2[$i]){
            return '2';
        }
        $result = $this->CompareMain($main1, $main2 , ++$i);
        return $result;
    }


    /**
     * @Route("test", name="test")
     */
    public function test() {

//
        $oCard = new Card();
        $toto = $oCard->getDeck();
        for ($i = 1; $i <= 7; $i++) {
            $aPokerHand[] = array_pop($toto);
        }

        $aPokerHand = array('8D', 'TD', '9S', '5S', 'JD', 'QD', 'KS');


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
     * @Route("test2", name="test2")
     */
    public function test2() {

//
        $oCard = new Card();
        $toto = $oCard->getDeck();
        for ($i = 1; $i <= 7; $i++) {
            $aPokerHand[] = array_pop($toto);
        }

        $aPokerHand = array('JC', '3S', '9S', '5S', 'JD', 'QD', 'KS');


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
     * @Route("/nextMain/{idTable}/{gagnant}", name="nextMain")
     */
    public function nextMainAction($idTable, Request $request, $gagnant){
        
        
        $verif = $this->init($request);
        if($verif == FALSE){ 
            return $this->redirect($this->generateUrl('_home'));
        }
        
        $playersAll = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable));
        $positionDealer = '';
        foreach($playersAll as $playerAll){
            if($playerAll->getDealer() == 1){
                $positionDealer = $playerAll->getPosition();
                $idDealer = $playerAll->getUser()->getId();
            }
        }
        
        $egalite = explode('+', $gagnant);
        if(count($egalite) == 1){
            $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->find($idTable);
            
            if($table->getTimeEnd() == null && $table->getTour() == 4 && $positionDealer != '' && $this->session->get('user')->getId() == $idDealer){
                
                $players = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'playMain' => 1));
                foreach ($players as $player){
                    if($player->getUser()->getPseudo() == $gagnant){

                        $player->setEncoursJetons($table->getPot() + $player->getEncoursJetons());
                        $table->setPot(null);
                        $table->setPackOfCards(null);
                        $this->em->persist($player);
                    }
                }
                foreach($playersAll as $playerAll){
                    $playerAll->setDealer(null);
                    $playerAll->setPlayMain(true);
                    $playerAll->setLastPlayer(null);
                    $playerAll->setTurn(null);
                    $playerAll->setAllIn(null);
                    $playerAll->setMiseJetons(null);
                    $playerAll->setCardOne(null);
                    $playerAll->setCardTwo(null);
                    $this->em->persist($playerAll);
                }
                
                $newPlaceDealer = $this->trouvePlace($table->getNbPosition(), $positionDealer, 1);
                $NewDealer = $this->em->getRepository('AfpaPokerGameBundle:Player')->findBy(array('tablePoker' => $idTable, 'position' => $newPlaceDealer));
                $NewDealer[0]->setDealer(1);
                $this->em->persist($NewDealer[0]);
                $table->setTour(null);
                $this->em->persist($table);
                $this->em->flush();
            }
            
            $array = $this->newMainAction($idTable, $request, true);
            
            return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
                        key($array) => current($array)
            ));

        }else{
            dump('a faire');
            die();
        }
        
        
            
        
        return $this->render('AfpaPokerGameBundle:TablePoker:gameView.html.twig', array(
        ));
    }
    
}
