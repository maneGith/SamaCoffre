<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AdminEntreprise
 *
 * @ORM\Table(name="admin_entreprise")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdminEntrepriseRepository")
 */
class AdminEntreprise
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
     * @ORM\OneToOne(targetEntity="User", cascade={"persist","remove"})
     * 
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     * @Assert\Valid
     */
    private $user;
    
    /**
     * @ORM\OneToOne(targetEntity="Entreprise", cascade={"persist","remove"})
     * 
     * @ORM\JoinColumn(name="entreprise", referencedColumnName="id")
     * @Assert\Valid
     */
    private $entreprise;

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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return AdminEntreprise
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
     * Set entreprise
     *
     * @param \AppBundle\Entity\Entreprise $entreprise
     *
     * @return AdminEntreprise
     */
    public function setEntreprise(\AppBundle\Entity\Entreprise $entreprise = null)
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    /**
     * Get entreprise
     *
     * @return \AppBundle\Entity\Entreprise
     */
    public function getEntreprise()
    {
        return $this->entreprise;
    }
}
