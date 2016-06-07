<?php

namespace Afpa\PokerGameBundle\Models;

Class Card {

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

}
