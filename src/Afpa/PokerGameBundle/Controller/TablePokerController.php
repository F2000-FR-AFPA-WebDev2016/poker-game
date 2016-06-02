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
    private $aPendingTables;
    private $generate;
    private $em;
    private $nbTable = 2;
    private $nameTable = array( 'Heads up', 'Heads up turbo');
    private $tableInit = array( 
                            'Heads up' => array( 'nbPos' => 2, 'factor' => 2, 'timeLevel' => 2, 'initialBet' => 15, 'stack' => 5000, 'buyIn' => 5, 'nbInscrit' => 0),
                            'Heads up turbo' => array( 'nbPos' => 2, 'factor' => 2, 'timeLevel' => 1, 'initialBet' => 100, 'stack' => 1500, 'buyIn' => 10, 'nbInscrit' => 0));
    
    public function createTable($em, $nb, $aPendingTables, $nameTable){
        for($i = 0; $i < $nb; $i++){
            $oTablePoker = new TablePoker();
            $oTablePoker->setName($nameTable);
            $oTablePoker->setNbPosition($this->tableInit[$nameTable]['nbPos']);
            $oTablePoker->setFactor($this->tableInit[$nameTable]['factor']);
            $oTablePoker->setTimeLevel($this->tableInit[$nameTable]['timeLevel']);
            $oTablePoker->setInitialBet($this->tableInit[$nameTable]['initialBet']);
            $oTablePoker->setStackTable($this->tableInit[$nameTable]['stack']);
            $oTablePoker->setBuyIn($this->tableInit[$nameTable]['buyIn']);
            $oTablePoker->setNbInscrit($this->tableInit[$nameTable]['nbInscrit']);
            $em->persist($oTablePoker);
            $em->flush();
            $aPendingTables[] = $oTablePoker;
        }
        return $aPendingTables;
    }
    
    public function recupTable(){
        $tables = array();
        $this->em = $em = $this->getDoctrine()->getManager();
        foreach($this->nameTable as $value){
            $query = $em->createQuery(
                                "SELECT t
                                FROM AfpaPokerGameBundle:TablePoker t
                                WHERE t.name = :name
                                AND t.nbPosition > t.nbInscrit"
                            )->setParameter(':name', $value);
                    $aPendingTables = $query->getResult();
            $nb = count($aPendingTables) == 0 ? $this->nbTable : $this->nbTable - count($aPendingTables);
            if($nb > 0){
                $tables = array_merge($tables, $this->createTable($em, $nb, $aPendingTables, $value));;
            }else{
                $tables = array_merge($tables, $aPendingTables);
            }
        }
        
        return $tables;
    }
    
    public function recupTablePleine(){
        $tables = array();
        $this->em = $em = $this->getDoctrine()->getManager();
        foreach($this->nameTable as $value){
            $query = $em->createQuery(
                                "SELECT t
                                FROM AfpaPokerGameBundle:TablePoker t
                                WHERE t.name = :name
                                AND t.nbPosition = t.nbInscrit"
                            )->setParameter(':name', $value);
                    $aPendingTables = $query->getResult();
            
            $tables = array_merge($tables, $aPendingTables);
        }
        return $tables;
    }
    
    public function PlayerInscrit($value, $user){
        $array = is_array(unserialize($value->getPlayerList())) ? unserialize($value->getPlayerList()) : array();
        $result = array( 'nb' => count($array), 'user' => false);
        foreach($array as $val){
            if($val->getIdPlayer() == $user){
                $result['user'] = true;
                return $result;
            }
        }
        return $result;
    }
    
    public function addFormTable($aPendingTables, $user, Request $request){
        foreach ($aPendingTables as $key => $value){
            $inscrit = $this->PlayerInscrit($value, $user);
            
        
            if($inscrit['nb'] < 2 ){
                $form = $this->createFormBuilder()
                    ->add('id', HiddenType::class, array('data' => $value->getId()))
                    ->add('action', HiddenType::class, array('data' => $inscrit['user'] == false ? 'in' : 'out' ))
                    ->add('inscription', SubmitType::class, array('label' => $inscrit['user'] == false ? 'S\'inscrire' : 'Se désinscrire'))
                    ->getForm();
                
                $form->handleRequest($request);
                
                if ($form->isSubmitted() && $form->isValid() && $form->getNormData()['id'] == $value->getId()) {
                    $this->inscriptionTable = array( 'action' => $form->getNormData()['action'], 'idTable' => $form->getNormData()['id'], 'arrayTable' => $key);
                    
                }
                
                $aPendingTables[$key] = array('form' => $form->createView(), 'table' => $value, );
            }else{
                $aPendingTables[$key] = array('table' => $value );
            }
        }
        
        return $aPendingTables;
    }
    
    public function initialiseTable($user, Request $request, $pleine = null){
        
        if($pleine == null){
            $aTables = $this->recupTable();
            $aPendingTables = $this->addFormTable($aTables, $user, $request);
            return $aPendingTables;
        }else{
            $aTables = $this->recupTablePleine();
        }
        
        return $aTables;
        
    }
   
    
    /**
     * @Route("/openTableRefresh", name="_open_table_refresh")
     */
    public function openTableRefreshAction(Request $request){
        $oSession = $request->getSession();
        return new \Symfony\Component\HttpFoundation\Response(dump($oSession->get('partie')));
    }

    
    /**
     * @Route("/listTableRefresh", name="_list_table_refresh")
     */
    public function listTableRefreshAction(Request $request){
        $aPendingTables = $this->miseAJourTable($request);
        return $this->render('AfpaPokerGameBundle:TablePoker:list_table_refresh.html.twig', array(
                    'pendingTables' => $aPendingTables,
            ));
    }
    
    
    /**
     * @Route("/listTable", name="_list_table")
     */
    public function listTableAction(Request $request) {
        if($request->getMethod('POST')){
            $this->miseAJourTable($request);
        }
        return $this->render('AfpaPokerGameBundle:TablePoker:list_table.html.twig');
            
    }
    
    
    /**
     * @Route("/listPartie", name="_list_partie")
     */
    public function listPartieAction(Request $request) {
        
        return $this->render('AfpaPokerGameBundle:TablePoker:list_partie.html.twig');
    }
    
    
    /**
     * @Route("/listPartie", name="_list_partie_refresh")
     */
    public function listPartieRefreshAction(Request $request) {
        $aPendingTables = $this->miseAJourTable($request, true);
        return $this->render('AfpaPokerGameBundle:TablePoker:list_partie_refresh.html.twig', array(
                    'pendingTables' => $aPendingTables,
            ));
    }
    
    public function miseAJourPlayerCredit(Request $request, $oPlayer, $credit){
        $user = $this->em->getRepository('AfpaPokerGameBundle:User')->findOneById($oPlayer->getIdPlayer());
        $newMonnaie = $user->getVirtualMoney() + $credit;
        $user->setVirtualMoney($newMonnaie);
        $this->em->flush();
        $userSession = $request->getSession()->get('user');
        $userSession->setVirtualMoney($newMonnaie);
        $oPlayer->setEnCoursJetons($oPlayer->getEnCoursJetons() + $credit);
        $oPlayer->setEnCoursMise($oPlayer->getEnCoursMise() - $credit);
        return $oPlayer;
    }
    
    public function miseAJourTable(Request $request, $pleine = null){
        $oSession = $request->getSession();

        if (!$oSession->get('user') instanceof User) {
            return $this->redirect($this->generateUrl('_home'));
        }
        
        $aPendingTables = $this->initialiseTable($oSession->get('user')->getId(), $request, $pleine);
        
        if($this->inscriptionTable != null){
            $table = $this->em->getRepository('AfpaPokerGameBundle:TablePoker')->findOneById($this->inscriptionTable['idTable']);

            $oPlayer = new Player($oSession->get('user'));

            $array = is_array(unserialize($table->getPlayerList())) ? unserialize($table->getPlayerList()) : array();
            $nbInscrit = $table->getNbInscrit();

            if($this->inscriptionTable['action'] == 'in'){
                $verif = false;
                foreach($array as $key => $value){
                    if($value->getIdPlayer() == $oPlayer->getIdPlayer() ){
                        $verif = true;
                        break;
                    }
                }
                if($verif == false){
                    $array[] = $oPlayer;
                    $nbInscrit++;
                    $oPlayer = $this->miseAJourPlayerCredit($request, $oPlayer, $table->getBuyIn()* -1);
                    if(!$oSession->get('partie')){
                        $oSession->set('partie', array( $this->inscriptionTable['idTable'] => $oPlayer));
                    }else{
                        $partie = $oSession->get('partie');
                        $partie[$this->inscriptionTable['idTable']] = $oPlayer;
                        $oSession->set('partie', $partie);
                    }
                }
                
                

            }elseif($this->inscriptionTable['action'] == 'out'){
                
                foreach($array as $key => $value){
                    if($value->getIdPlayer() == $oPlayer->getIdPlayer() ){
                        unset($array[$key]);
                        $nbInscrit--;
                        $array = array_values($array);

                        $oPlayer = $this->miseAJourPlayerCredit($request, $oPlayer, $table->getBuyIn() );
                        if(count($oSession->get('partie')) < 2){
                            
                            $oSession->remove('partie');
                        }else{
                            $partie = $oSession->get('partie');
                            unset($partie[$this->inscriptionTable['idTable']]) ;
                            $oSession->set('partie', $partie);
                        }
                        break;
                    }
                }
                
                        
            }
            $aPendingTables[$this->inscriptionTable['arrayTable']]['table']->setNbInscrit($nbInscrit);
           // dump($this->inscriptionTable); 
            //dump($aPendingTables); die ();

            $table->setPlayerList(serialize($array));
            $table->setNbInscrit($nbInscrit);
            $this->em->flush();
        }
        return $aPendingTables;
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
