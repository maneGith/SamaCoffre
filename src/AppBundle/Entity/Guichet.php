<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Guichet
 *
 * @ORM\Table(name="guichet")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GuichetRepository")
 */
class Guichet
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
     * @ORM\ManyToOne(targetEntity="Agence")
     */
    private $agence;

    /**
     * @ORM\OneToOne(targetEntity="User", cascade={"persist","remove"})
     * 
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     * @Assert\Valid
     */
    private $user;
    
     /**
     * @var string
     *
     * @ORM\Column(name="droitauto", type="string", length=255, nullable=true)
     */
    private $droitauto;


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
     * Set agence
     *
     * @param \AppBundle\Entity\Agence $agence
     *
     * @return Guichet
     */
    public function setAgence(\AppBundle\Entity\Agence $agence = null)
    {
        $this->agence = $agence;

        return $this;
    }

    /**
     * Get agence
     *
     * @return \AppBundle\Entity\Agence
     */
    public function getAgence()
    {
        return $this->agence;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Guichet
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set droitauto
     *
     * @param string $droitauto
     *
     * @return Guichet
     */
    public function setDroitauto($droitauto)
    {
        $this->droitauto = $droitauto;

        return $this;
    }

    /**
     * Get droitauto
     *
     * @return string
     */
    public function getDroitauto()
    {
        return $this->droitauto;
    }
}
