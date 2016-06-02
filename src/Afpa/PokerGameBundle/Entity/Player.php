<?php

namespace Afpa\PokerGameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="Afpa\PokerGameBundle\Repository\PlayerRepository")
 */
class Player
{
    
    /**
    * @ORM\ManyToOne(targetEntity="User", inversedBy="players")
    * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
    */
    protected $user;
    
    /**
    * @ORM\ManyToOne(targetEntity="TablePoker", inversedBy="players")
    * @ORM\JoinColumn(name="table_id", referencedColumnName="id", nullable=false)
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
     * @var int
     *
     * @ORM\Column(name="postion", type="integer")
     */
    private $postion;

    /**
     * @var string
     *
     * @ORM\Column(name="cardOne", type="string", length=255)
     */
    private $cardOne;

    /**
     * @var string
     *
     * @ORM\Column(name="cardTwo", type="string", length=255)
     */
    private $cardTwo;

    /**
     * @var int
     *
     * @ORM\Column(name="encoursJetons", type="integer")
     */
    private $encoursJetons;

    /**
     * @var int
     *
     * @ORM\Column(name="encoursMise", type="integer")
     */
    private $encoursMise;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set postion
     *
     * @param integer $postion
     *
     * @return Player
     */
    public function setPostion($postion)
    {
        $this->postion = $postion;

        return $this;
    }

    /**
     * Get postion
     *
     * @return int
     */
    public function getPostion()
    {
        return $this->postion;
    }

    /**
     * Set cardOne
     *
     * @param string $cardOne
     *
     * @return Player
     */
    public function setCardOne($cardOne)
    {
        $this->cardOne = $cardOne;

        return $this;
    }

    /**
     * Get cardOne
     *
     * @return string
     */
    public function getCardOne()
    {
        return $this->cardOne;
    }

    /**
     * Set cardTwo
     *
     * @param string $cardTwo
     *
     * @return Player
     */
    public function setCardTwo($cardTwo)
    {
        $this->cardTwo = $cardTwo;

        return $this;
    }

    /**
     * Get cardTwo
     *
     * @return string
     */
    public function getCardTwo()
    {
        return $this->cardTwo;
    }

    /**
     * Set encoursJetons
     *
     * @param integer $encoursJetons
     *
     * @return Player
     */
    public function setEncoursJetons($encoursJetons)
    {
        $this->encoursJetons = $encoursJetons;

        return $this;
    }

    /**
     * Get encoursJetons
     *
     * @return int
     */
    public function getEncoursJetons()
    {
        return $this->encoursJetons;
    }

    /**
     * Set encoursMise
     *
     * @param integer $encoursMise
     *
     * @return Player
     */
    public function setEncoursMise($encoursMise)
    {
        $this->encoursMise = $encoursMise;

        return $this;
    }

    /**
     * Get encoursMise
     *
     * @return int
     */
    public function getEncoursMise()
    {
        return $this->encoursMise;
    }
}

