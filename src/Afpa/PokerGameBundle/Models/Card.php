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
    private static $colorCardSta = array('S', 'H', 'D', 'C');
//
    private $valueCard = array('X', 'K', 'Q', 'J', 'T', '9', '8', '7', '6', '5', '4', '3', '2');
    private static $valueCardSta = array('X', 'K', 'Q', 'J', 'T', '9', '8', '7', '6', '5', '4', '3', '2');
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

    public static function recupValueCard($type) {
        if ($type == 'color') {
            return self::$colorCardSta;
        } else {
            return self::$valueCardSta;
        }
    }

    public function getValueCard() {
        return $this->valueCard;
    }

}
