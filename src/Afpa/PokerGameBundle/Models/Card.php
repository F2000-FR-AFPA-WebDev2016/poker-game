<?php

namespace Afpa\PokerGameBundle\Models;

class Card {

    const FLUSH = '9-flush';
    const CARRE = '8-carre';
    const FULL = '7-full';
    const COULEUR = '6-couleur';
    const QUINTE = '5-quinte';
    const BRELAN = '4-brelan';
    const DEUXPAIRES = '3-deux paires';
    const PAIRE = '2-une paire';
    const CARTE = '1-carte';

    protected $id;
//Pique, Coeur, Carreau, Trèfle => Spades, Hearts, Diamonds, Clubs
    private $colorCard = array('S', 'H', 'D', 'C');
//
    private $valueCard = array('X', 'K', 'Q', 'J', 'T', '9', '8', '7', '6', '5', '4', '3', '2');
    protected $deck;

    public function __construct() {

    }

    public function getDeck() {
        foreach ($this->valueCard as $value) {
            foreach ($this->colorCard as $color) {
                $aDeck[] = $value . $color;
            }
        }
        $var = date("s");
        for ($i = 10; $i < $var + 10; $i++) {
            shuffle($aDeck);
        }
        return($aDeck);
    }

    public static function SortPokerHand($aPokerHand) {
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

    public static function isSameColor($aPokerHand) {
        $aArray = array();
        foreach ($aPokerHand as $sCard) {
            $aCardSub = substr($sCard, 1, 1);
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
        if (max($aArray) >= 5) {
            return true;
        }
        return false;
    }

    public function isSuite($aPokerHand) {
        $aArray = array();
        foreach ($aPokerHand as $sCard) {
            $search = substr($sCard, 0, 1);
            $rang = array_search($search, $this->valueCard);
            $aArray[$sCard] = $rang;
        }
        asort($aArray);

        $aArrayUnique = array_values(array_unique($aArray));

        $res1 = false;
        $res2 = false;
        $res3 = false;

        if (array_key_exists(6, $aArrayUnique)) {
            $res1 = (($aArrayUnique[6] - $aArrayUnique[2]) == 4);
        }
        if (array_key_exists(5, $aArrayUnique)) {
            $res2 = (($aArrayUnique[5] - $aArrayUnique[1]) == 4);
        }
        if (array_key_exists(4, $aArrayUnique)) {
            $res3 = (($aArrayUnique[4] - $aArrayUnique[0]) == 4);
        }
        if ($res1 or $res2 or $res3) {
            return true;
        }
        return false;
    }

    public function EvalPokerHand($sortPG, $bIsColor, $bIsSuite) {
        arsort($sortPG);
        $iEval1 = array_values($sortPG)[0];
        $iEval2 = array_values($sortPG)[1];
        if ($bIsColor && $bIsSuite) {
            return self::FLUSH;
        } elseif ($iEval1 == 4) {
            return self::CARRE;
        } elseif ($iEval1 == 3 && $iEval2 == 2) {
            return self::FULL;
        } elseif ($bIsColor) {
            return self::COULEUR;
        } elseif ($bIsSuite) {
            return self::QUINTE;
        } elseif ($iEval1 == 3) {
            return self::BRELAN;
        } elseif ($iEval1 == 2 && $iEval2 == 2) {
            return self::DEUXPAIRES;
        } elseif ($iEval1 == 2) {
            return self::PAIRE;
        } else {
            return self::CARTE;
        }
    }

}
