<?php

namespace Afpa\PokerGameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Afpa\PokerGameBundle\Repository\UserRepository")
 */
class User
{
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
     * @ORM\Column(name="pseudo", type="string", length=50, unique=true)
     */
    private $pseudo;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=100, unique=true)
     */
    private $mail;

    /**
     * @var float
     *
     * @ORM\Column(name="virtual_money", type="float")
     */
    private $virtualMoney;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_last_credit", type="datetime")
     */
    private $timeLastCredit;


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
     * Set pseudo
     *
     * @param string $pseudo
     *
     * @return User
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * Get pseudo
     *
     * @return string
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set mail
     *
     * @param string $mail
     *
     * @return User
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set virtualMoney
     *
     * @param float $virtualMoney
     *
     * @return User
     */
    public function setVirtualMoney($virtualMoney)
    {
        $this->virtualMoney = $virtualMoney;

        return $this;
    }

    /**
     * Get virtualMoney
     *
     * @return float
     */
    public function getVirtualMoney()
    {
        return $this->virtualMoney;
    }

    /**
     * Set timeLastCredit
     *
     * @param \DateTime $timeLastCredit
     *
     * @return User
     */
    public function setTimeLastCredit($timeLastCredit)
    {
        $this->timeLastCredit = $timeLastCredit;

        return $this;
    }

    /**
     * Get timeLastCredit
     *
     * @return \DateTime
     */
    public function getTimeLastCredit()
    {
        return $this->timeLastCredit;
    }
}

