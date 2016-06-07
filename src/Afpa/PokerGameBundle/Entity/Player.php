<?php

namespace Afpa\PokerGameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="Afpa\PokerGameBundle\Repository\PlayerRepository")
 */
class Player {

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="players")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="TablePoker", inversedBy="players")
     * @ORM\JoinColumn(name="table_poker_id", referencedColumnName="id", nullable=false)
     */
    protected $tablePoker;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="cardOne", type="string", length=255, nullable=true)
     */
    private $cardOne;

    /**
     * @var string
     *
     * @ORM\Column(name="cardTwo", type="string", length=255, nullable=true)
     */
    private $cardTwo;

    /**
     * @var int
     *
     * @ORM\Column(name="encoursJetons", type="integer", nullable=true)
     */
    private $encoursJetons;

    /**
     * @var int
     *
     * @ORM\Column(name="miseJetons", type="integer", nullable=true)
     */
    private $miseJetons;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var boolean
     *
     * @ORM\Column(name="turn", type="boolean",  options={"default":false})
     */
    private $turn;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dealer", type="boolean",  options={"default":false})
     */
    private $dealer;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set cardOne
     *
     * @param string $cardOne
     *
     * @return Player
     */
    public function setCardOne($cardOne) {
        $this->cardOne = $cardOne;

        return $this;
    }

    /**
     * Get cardOne
     *
     * @return string
     */
    public function getCardOne() {
        return $this->cardOne;
    }

    /**
     * Set cardTwo
     *
     * @param string $cardTwo
     *
     * @return Player
     */
    public function setCardTwo($cardTwo) {
        $this->cardTwo = $cardTwo;

        return $this;
    }

    /**
     * Get cardTwo
     *
     * @return string
     */
    public function getCardTwo() {
        return $this->cardTwo;
    }

    /**
     * Set encoursJetons
     *
     * @param integer $encoursJetons
     *
     * @return Player
     */
    public function setEncoursJetons($encoursJetons) {
        $this->encoursJetons = $encoursJetons;

        return $this;
    }

    /**
     * Get encoursJetons
     *
     * @return int
     */
    public function getEncoursJetons() {
        return $this->encoursJetons;
    }

    /**
     * Set miseJetons
     *
     * @param integer $miseJetons
     *
     * @return Player
     */
    public function setMiseJetons($miseJetons) {
        $this->miseJetons = $miseJetons;

        return $this;
    }

    /**
     * Get miseJetons
     *
     * @return int
     */
    public function getMiseJetons() {
        return $this->miseJetons;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Player
     */
    public function setPosition($position) {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition() {
        return $this->position;
    }

    public function setTurn($turn) {
        $this->turn = $turn;

        return $this;
    }

    public function getTurn() {
        return $this->turn;
    }

    /**
     * Set user
     *
     * @param \Afpa\PokerGameBundle\Entity\User $user
     *
     * @return Player
     */
    public function setUser(\Afpa\PokerGameBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Afpa\PokerGameBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set tablePoker
     *
     * @param \Afpa\PokerGameBundle\Entity\TablePoker $tablePoker
     *
     * @return Player
     */
    public function setTablePoker(\Afpa\PokerGameBundle\Entity\TablePoker $tablePoker = null) {
        $this->tablePoker = $tablePoker;

        return $this;
    }

    /**
     * Get tablePoker
     *
     * @return \Afpa\PokerGameBundle\Entity\TablePoker
     */
    public function getTablePoker() {
        return $this->tablePoker;
    }


    /**
     * Set dealer
     *
     * @param boolean $dealer
     *
     * @return Player
     */
    public function setDealer($dealer)
    {
        $this->dealer = $dealer;

        return $this;
    }

    /**
     * Get dealer
     *
     * @return boolean
     */
    public function getDealer()
    {
        return $this->dealer;
    }
}
