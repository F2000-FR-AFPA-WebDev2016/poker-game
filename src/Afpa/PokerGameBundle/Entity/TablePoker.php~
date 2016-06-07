<?php

namespace Afpa\PokerGameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TablePoker
 *
 * @ORM\Table(name="table_poker")
 * @ORM\Entity(repositoryClass="Afpa\PokerGameBundle\Repository\TablePokerRepository")
 */
class TablePoker {

    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="tablePoker")
     */
    protected $players;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_position", type="integer")
     */
    private $nbPosition;

    /**
     * @var int
     *
     * @ORM\Column(name="nombre_inscrit", type="integer", nullable=true)
     */
    private $nbInscrit;

    /**
     * @var int
     *
     * @ORM\Column(name="time_level", type="integer")
     */
    private $timeLevel;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_start", type="datetime", nullable=true)
     */
    private $timeStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_end", type="datetime", nullable=true)
     */
    private $timeEnd;

    /**
     * @var int
     *
     * @ORM\Column(name="initial_bet", type="integer")
     */
    private $initialBet;

    /**
     * @var int
     *
     * @ORM\Column(name="factor", type="integer")
     */
    private $factor;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="stack_table", type="integer")
     */
    private $stackTable;

    /**
     * @var int
     *
     * @ORM\Column(name="buy_in", type="integer")
     */
    private $buyIn;

    /**
     * @var string
     *
     * @ORM\Column(name="oC1", type="string", length=2, nullable=true)
     */
    private $oC1;

    /**
     * @var string
     *
     * @ORM\Column(name="oC2", type="string", length=2, nullable=true)
     */
    private $oC2;

    /**
     * @var string
     *
     * @ORM\Column(name="oC3", type="string", length=2, nullable=true)
     */
    private $oC3;

    /**
     * @var string
     *
     * @ORM\Column(name="oC4", type="string", length=2, nullable=true)
     */
    private $oC4;

    /**
     * @var string
     *
     * @ORM\Column(name="oC5", type="string", length=2, nullable=true)
     */
    private $oC5;

    /**
     * @var string
     *
     * @ORM\Column(name="player_list", type="text", nullable=true)
     */
    private $playerList;

    /**
     * @var int
     *
     * @ORM\Column(name="pot", type="integer", nullable=true)
     */
    private $pot;

    /**
     * @var string
     *
     * @ORM\Column(name="pack_of_cards", type="text", nullable=true)
     */
    private $packOfCards;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nbInscrit
     *
     * @param integer $nbInscrit
     *
     * @return TablePoker
     */
    public function setNbInscrit($nbInscrit) {
        $this->nbInscrit = $nbInscrit;

        return $this;
    }

    /**
     * Get nbInscrit
     *
     * @return int
     */
    public function getNbInscrit() {
        return $this->nbInscrit;
    }

    /**
     * Set nbPosition
     *
     * @param integer $nbPosition
     *
     * @return TablePoker
     */
    public function setNbPosition($nbPosition) {
        $this->nbPosition = $nbPosition;

        return $this;
    }

    /**
     * Get nbPosition
     *
     * @return int
     */
    public function getNbPosition() {
        return $this->nbPosition;
    }

    /**
     * Set timeLevel
     *
     * @param integer $timeLevel
     *
     * @return TablePoker
     */
    public function setTimeLevel($timeLevel) {
        $this->timeLevel = $timeLevel;

        return $this;
    }

    /**
     * Get timeLevel
     *
     * @return int
     */
    public function getTimeLevel() {
        return $this->timeLevel;
    }

    /**
     * Set timeStart
     *
     * @param \DateTime $timeStart
     *
     * @return TablePoker
     */
    public function setTimeStart($timeStart) {
        $this->timeStart = $timeStart;

        return $this;
    }

    /**
     * Get timeStart
     *
     * @return \DateTime
     */
    public function getTimeStart() {
        return $this->timeStart;
    }

    /**
     * Set timeEnd
     *
     * @param \DateTime $timeEnd
     *
     * @return TablePoker
     */
    public function setTimeEnd($timeEnd) {
        $this->timeEnd = $timeEnd;

        return $this;
    }

    /**
     * Get timeEnd
     *
     * @return \DateTime
     */
    public function getTimeEnd() {
        return $this->timeEnd;
    }

    /**
     * Set initialBet
     *
     * @param integer $initialBet
     *
     * @return TablePoker
     */
    public function setInitialBet($initialBet) {
        $this->initialBet = $initialBet;

        return $this;
    }

    /**
     * Get initialBet
     *
     * @return int
     */
    public function getInitialBet() {
        return $this->initialBet;
    }

    /**
     * Set stackTable
     *
     * @param integer $stackTable
     *
     * @return TablePoker
     */
    public function setStackTable($stackTable) {
        $this->stackTable = $stackTable;

        return $this;
    }

    /**
     * Get stackTable
     *
     * @return int
     */
    public function getStackTable() {
        return $this->stackTable;
    }

    /**
     * Set factor
     *
     * @param integer $factor
     *
     * @return TablePoker
     */
    public function setFactor($factor) {
        $this->factor = $factor;

        return $this;
    }

    /**
     * Get factor
     *
     * @return int
     */
    public function getFactor() {
        return $this->factor;
    }

    /**
     * Set buyIn
     *
     * @param integer $buyIn
     *
     * @return TablePoker
     */
    public function setBuyIn($buyIn) {
        $this->buyIn = $buyIn;

        return $this;
    }

    /**
     * Get buyIn
     *
     * @return int
     */
    public function getBuyIn() {
        return $this->buyIn;
    }

    /**
     * Set openCards
     *
     * @param string $openCards
     *
     * @return TablePoker
     */
    public function setOpenCards($openCards) {
        $this->openCards = $openCards;

        return $this;
    }

    /**
     * Get openCards
     *
     * @return string
     */
    public function getOpenCards() {
        return $this->openCards;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return TablePoker
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set playerList
     *
     * @param string $playerList
     *
     * @return TablePoker
     */
    public function setPlayerList($playerList) {
        $this->playerList = $playerList;

        return $this;
    }

    /**
     * Get playerList
     *
     * @return string
     */
    public function getPlayerList() {
        return $this->playerList;
    }

    /**
     * Set pot
     *
     * @param integer $pot
     *
     * @return TablePoker
     */
    public function setPot($pot) {
        $this->pot = $pot;

        return $this;
    }

    /**
     * Get pot
     *
     * @return int
     */
    public function getPot() {
        return $this->pot;
    }

    /**
     * Set packOfCards
     *
     * @param string $packOfCards
     *
     * @return TablePoker
     */
    public function setPackOfCards($packOfCards) {
        $this->packOfCards = $packOfCards;

        return $this;
    }

    /**
     * Get packOfCards
     *
     * @return string
     */
    public function getPackOfCards() {
        return $this->packOfCards;
    }

    /**
     * Add user
     *
     * @param \Afpa\PokerGameBundle\Entity\User $user
     *
     * @return TablePoker
     */
    public function addUser(\Afpa\PokerGameBundle\Entity\User $user) {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Afpa\PokerGameBundle\Entity\User $user
     */
    public function removeUser(\Afpa\PokerGameBundle\Entity\User $user) {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * Add player
     *
     * @param \Afpa\PokerGameBundle\Entity\Player $player
     *
     * @return TablePoker
     */
    public function addPlayer(\Afpa\PokerGameBundle\Entity\Player $player) {
        $this->players[] = $player;

        return $this;
    }

    /**
     * Remove player
     *
     * @param \Afpa\PokerGameBundle\Entity\Player $player
     */
    public function removePlayer(\Afpa\PokerGameBundle\Entity\Player $player) {
        $this->players->removeElement($player);
    }

    /**
     * Get players
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlayers() {
        return $this->players;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->players = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set oC1
     *
     * @param string $oC1
     *
     * @return TablePoker
     */
    public function setOC1($oC1)
    {
        $this->oC1 = $oC1;

        return $this;
    }

    /**
     * Get oC1
     *
     * @return string
     */
    public function getOC1()
    {
        return $this->oC1;
    }

    /**
     * Set oC2
     *
     * @param string $oC2
     *
     * @return TablePoker
     */
    public function setOC2($oC2)
    {
        $this->oC2 = $oC2;

        return $this;
    }

    /**
     * Get oC2
     *
     * @return string
     */
    public function getOC2()
    {
        return $this->oC2;
    }

    /**
     * Set oC3
     *
     * @param string $oC3
     *
     * @return TablePoker
     */
    public function setOC3($oC3)
    {
        $this->oC3 = $oC3;

        return $this;
    }

    /**
     * Get oC3
     *
     * @return string
     */
    public function getOC3()
    {
        return $this->oC3;
    }

    /**
     * Set oC4
     *
     * @param string $oC4
     *
     * @return TablePoker
     */
    public function setOC4($oC4)
    {
        $this->oC4 = $oC4;

        return $this;
    }

    /**
     * Get oC4
     *
     * @return string
     */
    public function getOC4()
    {
        return $this->oC4;
    }

    /**
     * Set oC5
     *
     * @param string $oC5
     *
     * @return TablePoker
     */
    public function setOC5($oC5)
    {
        $this->oC5 = $oC5;

        return $this;
    }

    /**
     * Get oC5
     *
     * @return string
     */
    public function getOC5()
    {
        return $this->oC5;
    }
}
