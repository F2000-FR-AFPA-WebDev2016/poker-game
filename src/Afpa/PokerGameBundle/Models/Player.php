<?php

namespace Afpa\PokerGameBundle\Models;

use Afpa\PokerGameBundle\Entity\User;

Class Player {

    protected $idPlayer;
    protected $position;
    protected $c1;
    protected $c2;
    protected $enCoursJetons;
    protected $enCoursMise;

    public function __construct(User $oUser) {
        $this->idPlayer = $oUser->getId();
        $this->enCoursJetons = $oUser->getVirtualMoney();
    }

    public function getIdPlayer() {
        return $this->idPlayer;
    }

    public function getPosition() {
        return $this->position;
    }

    public function getC1() {
        return $this->c1;
    }

    public function getC2() {
        return $this->c2;
    }

    public function getEnCoursJetons() {
        return $this->enCoursJetons;
    }

    public function getEnCoursMise() {
        return $this->enCoursMise;
    }

    public function setIdPlayer($idPlayer) {
        $this->idPlayer = $idPlayer;
    }

    public function setPosition($position) {
        $this->position = $position;
    }

    public function setC1($c1) {
        $this->c1 = $c1;
    }

    public function setC2($c2) {
        $this->c2 = $c2;
    }

    public function setEnCoursJetons($enCoursJetons) {
        $this->enCoursJetons = $enCoursJetons;
    }

    public function setEnCoursMise($enCoursMise) {
        $this->enCoursMise = $enCoursMise;
    }

}
