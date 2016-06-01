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
     * @ORM\Column(name="open_cards", type="string", length=255, nullable=true)
     */
    private $openCards;

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

}
