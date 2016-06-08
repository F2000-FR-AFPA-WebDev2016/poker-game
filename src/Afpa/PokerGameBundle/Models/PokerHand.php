<?php

namespace Afpa\PokerGameBundle\Models;

use Afpa\PokerGameBundle\Models\Card;

/**
 * Description of PokerHand
 *
 * @author moi
 */
class PokerHand {

    const FLUSH = 'Flush';
    const CARRE = 'Carré';
    const FULL = 'Full';
    const COULEUR = 'Couleur';
    const QUINTE = 'Quinte';
    const BRELAN = 'Brelan';
    const DEUXPAIRES = 'Deux paires';
    const PAIRE = 'Une paire';
    const CARTE = 'Carte';

    private $bestHand;
    private $ForceHand;
    private $typeHand;

    public function __construct($aPokerHand) {
        $sortPH = PokerHand::SortPokerHand($aPokerHand);
        $ahandColor = PokerHand::handSameColor($aPokerHand);
        $bIsSuite = $this->handSuite($aPokerHand);
        arsort($sortPH);
        $iEval1 = array_values($sortPH)[0];
        $iEval2 = array_values($sortPH)[1];
        if (is_array($ahandColor) && is_array($bIsSuite)) {
            $this->setForceHand(9);
            $this->setTypeHand(self::FLUSH);
        } elseif ($iEval1 == 4) {
            $this->setForceHand(8);
            $this->setTypeHand(self::CARRE);
        } elseif ($iEval1 == 3 && $iEval2 == 2) {
            $this->setForceHand(7);
            $this->setTypeHand(self::FULL);
        } elseif (is_array($ahandColor)) {
            $this->setForceHand(6);
            $this->setTypeHand(self::COULEUR);
        } elseif (is_array($bIsSuite)) {
            $this->setForceHand(5);
            $this->setTypeHand(self::QUINTE);
        } elseif ($iEval1 == 3) {
            $this->setForceHand(4);
            $this->setTypeHand(self::BRELAN);
        } elseif ($iEval1 == 2 && $iEval2 == 2) {
            $this->setForceHand(3);
            $this->setTypeHand(self::DEUXPAIRES);
        } elseif ($iEval1 == 2) {
            $this->setForceHand(2);
            $this->setTypeHand(self::PAIRE);
        } else {
            $this->setForceHand(1);
            $this->setTypeHand(self::CARTE);
        }
    }

    public function getBestHand() {
        return $this->bestHand;
    }

    public function getForceHand() {
        return $this->ForceHand;
    }

    public function getTypeHand() {
        return $this->typeHand;
    }

    public function setBestHand($bestHand) {
        $this->bestHand = $bestHand;
    }

    public function setForceHand($ForceHand) {
        $this->ForceHand = $ForceHand;
    }

    public function setTypeHand($typeHand) {
        $this->typeHand = $typeHand;
    }

    public function SortPokerHand($aPokerHand) {
        $aArray = array();
        foreach ($aPokerHand as $sCard) {
            $aCardSub = substr($sCard, 0, 1);
            if (count($aArray) > 0) {
                if (isset($aArray[$aCardSub])) {
                    $aArray[$aCardSub] = $aArray[$aCardSub] + 1;
                } else {
                    $aArray[$aCardSub] = 1;
                }
            } else {
                $aArray[$aCardSub] = 1;
            }
        }
        return($aArray);
    }

    public function handSameColor($aPokerHand) {
        $aArray = array();
        foreach ($aPokerHand as $sCard) {
            $aCardSub = substr($sCard, 1, 1);
            if (count($aArray) > 0) {

                if (array_key_exists($aCardSub, $aArray)) {
                    $aArray[$aCardSub][] = $sCard;
                } else {
                    $aArray[$aCardSub] = array($sCard);
                }
            } else {
                $aArray = array($aCardSub => array($sCard));
            }
        }
        foreach ($aArray as $key => $value) {

            if (count($value) >= 5) {
                dump($value);
                self::mySort($value);
                return $value;
            }
        }
        return false;
    }

    public function handSuite($aPokerHand) {
        $aArray = array();
        $oCard = new card();
        foreach ($aPokerHand as $sCard) {
            $search = substr($sCard, 0, 1);
            $rang = array_search($search, $oCard->getValueCard());
            $aArray[$sCard] = $rang;
        }
        asort($aArray);
        $cpt = 1;
        dump($aArray);
        $aTemp = array_values($aArray);

        for ($i = 0; $i < count($aArray) - 1; $i++) {
            if ($aTemp[$i + 1] == $aTemp[$i] + 1) {
                $cpt ++;
            } elseif (($aTemp[$i + 1] == $aTemp[$i])) {

            } else {
                $cpt = 1;
            }
        }
        if ($cpt == 5) {
            return $aArray;
        }
        return false;
    }

    public function EvalPokerHand($aPokerHand) {

    }

    public function mySort($aArray) {
        $valueCard = array('X', 'K', 'Q', 'J', 'T', '9', '8', '7', '6', '5', '4', '3', '2');
        dump($aArray);
        foreach ($aArray as $card) {
            $key = array_search(substr($card, 0, 1), $valueCard);
            $tabTemp[$card] = $key;
        }
        asort($tabTemp);
        //$finalHand = array_slice($tabTemp, 0, 5);
        return $tabTemp;
    }

}
