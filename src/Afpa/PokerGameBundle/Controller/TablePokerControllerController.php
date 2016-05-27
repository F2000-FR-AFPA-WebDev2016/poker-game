<?php

namespace Afpa\PokerGameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class TablePokerControllerController extends Controller
{
    /**
     * @Route("/listTable")
     */
    public function listTableAction()
    {
        return $this->render('AfpaPokerGameBundle:TablePokerController:list_table.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/play/{id}")
     */
    public function playAction($id)
    {
        return $this->render('AfpaPokerGameBundle:TablePokerController:play.html.twig', array(
            // ...
        ));
    }

}
