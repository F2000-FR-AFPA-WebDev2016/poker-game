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
        $ahandColorSort = is_array($ahandColor) ? self::mySort($ahandColor) : false;
        $iFlush = is_array($ahandColorSort) ? max($ahandColorSort) - min($ahandColorSort) : 0;
        $bIsFlush = ($iFlush == 4);
        $aHandSuite = $this->handSuite($aPokerHand);

        $iEval1 = array_values($sortPH)[0]['cpt'];
        $iEval2 = array_values($sortPH)[1]['cpt'];
        $iRang1 = array_values($sortPH)[0]['rang'];
        $iRang2 = array_values($sortPH)[1]['rang'];
        $iRang3 = array_key_exists(2, array_values($sortPH)) ? array_values($sortPH)[2]['rang'] : "";
        $iRang4 = array_key_exists(3, array_values($sortPH)) ? array_values($sortPH)[3]['rang'] : "";
        $iRang5 = array_key_exists(4, array_values($sortPH)) ? array_values($sortPH)[4]['rang'] : "";


        if ($bIsFlush) {
            $this->setForceHand(9);
            $this->setTypeHand(self::FLUSH);
        } elseif ($iEval1 == 4) {
            $fH = '8' . '-' . $iRang1 . '-' . $iRang2;
            $this->setForceHand($fH);
            $this->setTypeHand(self::CARRE);
        } elseif ($iEval1 == 3 && $iEval2 == 2) {
            $fH = '7' . '-' . $iRang1 . '-' . $iRang2;
            $this->setForceHand($fH);
            $this->setTypeHand(self::FULL);
        } elseif ($ahandColorSort) {
            $fH = '6' . '-' . array_values($ahandColorSort)[0] . '-' . array_values($ahandColorSort)[1] . '-' . array_values($ahandColorSort)[2] . '-' . array_values($ahandColorSort)[3] . '-' . array_values($ahandColorSort)[4];
            $this->setForceHand($fH);
            $this->setTypeHand(self::COULEUR);
        } elseif ($aHandSuite) {
            $fH = '5' . '-' . array_values($aHandSuite)[0] . '-' . array_values($aHandSuite)[1] . '-' . array_values($aHandSuite)[2] . '-' . array_values($aHandSuite)[3] . '-' . array_values($aHandSuite)[4];
            $this->setForceHand($fH);
            $this->setTypeHand(self::QUINTE);
        } elseif ($iEval1 == 3) {
            $fH = '4' . '-' . $iRang1 . '-' . $iRang2 . '-' . $iRang3;
            $this->setForceHand($fH);
            $this->setTypeHand(self::BRELAN);
        } elseif ($iEval1 == 2 && $iEval2 == 2) {
            $fH = '3' . '-' . $iRang1 . '-' . $iRang2 . '-' . $iRang3;
            $this->setForceHand($fH);
            $this->setTypeHand(self::DEUXPAIRES);
        } elseif ($iEval1 == 2) {
            $fH = '2' . '-' . $iRang1 . '-' . $iRang2 . '-' . $iRang3 . '-' . $iRang4;
            $this->setForceHand($fH);
            $this->setTypeHand(self::PAIRE);
        } else {
            $fH = '1' . '-' . $iRang1 . '-' . $iRang2 . '-' . $iRang3 . '-' . $iRang4 . '-' . $iRang5;
            $this->setForceHand($fH);
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
                    $aArray[$aCardSub]['cpt'] = $aArray[$aCardSub]['cpt'] + 1;
                } else {
                    $aArray[$aCardSub] = array(
                        'cpt' => 1,
                        'rang' => $this->rang($aCardSub)
                    );
                }
            } else {
                $aArray[$aCardSub] = array(
                    'cpt' => 1,
                    'rang' => $this->rang($aCardSub)
                );
            }
        }
        arsort($aArray);

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
                return $value;
            }
        }
        return false;
    }

    public function handSuite($aPokerHand) {
        $aArray = array();
        $oCard = new card();
        foreach ($aPokerHand as $sCard) {
            $aArray[$sCard] = $this->rang(substr($sCard, 0, 1));
        }

        asort($aArray);

        $tabTemp1 = array_unique(array_slice($aArray, 0, 5));
        $tabTemp2 = array_unique(array_slice($aArray, 1, 5));
        $tabTemp3 = array_unique(array_slice($aArray, 2, 6));
        $tabTemp4 = array_unique(array_slice($aArray, 0, 6));
        $tabTemp5 = array_unique(array_slice($aArray, 1, 6));
        $tabTemp6 = array_unique($aPokerHand);


        for ($i = 6; $i >= 1; $i--) {
            $hand = ${'tabTemp' . $i};
            $res = max($hand) - min($hand);
            if ($res == 4 && count($hand) == 5) {
                arsort($hand);
                return ($hand);
            }
        }
        return false;
    }

    public function mySort($aArray) {
        $oCard = new card();
        foreach ($aArray as $card) {
            $key = $this->rang(substr($card, 0, 1));
            $tabTemp[$card] = $key;
        }
        arsort($tabTemp);

        $aFlush = $this->handSuite($tabTemp);
        $finalHand = array_slice($tabTemp, 0, 5);

        return ($aFlush ? $aFlush : $finalHand);
    }

    public function rang($sValueCard) {
        $oCard = new card();
        $rang = 14 - array_search($sValueCard, $oCard->getValueCard());
        return $rang;
    }

}
