<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccesReferenceService
 *
 * @ORM\Table(name="acces_reference_service")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AccesReferenceServiceRepository")
 */
class AccesReferenceService
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
     * @ORM\Column(name="reference", type="string", length=255)
     */
    private $reference;
    
    /**
     * @ORM\ManyToOne(targetEntity="EntrepriseService")
     * 
     * @ORM\JoinColumn(name="service", referencedColumnName="id")
     */
    private $service;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * 
     * @ORM\JoinColumn(name="usager", referencedColumnName="id")
     */
    private $usager;
    
    /**
     * @ORM\ManyToOne(targetEntity="Guichet")
     * 
     * @ORM\JoinColumn(name="guichet", referencedColumnName="id", nullable=true)
     */
    private $guichet;
    
    

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
     * Set reference
     *
     * @param string $reference
     *
     * @return AccesReferenceService
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set service
     *
     * @param \AppBundle\Entity\EntrepriseService $service
     *
     * @return AccesReferenceService
     */
    public function setService(\AppBundle\Entity\EntrepriseService $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \AppBundle\Entity\EntrepriseService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set usager
     *
     * @param \AppBundle\Entity\User $usager
     *
     * @return AccesReferenceService
     */
    public function setUsager(\AppBundle\Entity\User $usager = null)
    {
        $this->usager = $usager;

        return $this;
    }

    /**
     * Get usager
     *
     * @return \AppBundle\Entity\User
     */
    public function getUsager()
    {
        return $this->usager;
    }

    /**
     * Set guichet
     *
     * @param \AppBundle\Entity\Guichet $guichet
     *
     * @return AccesReferenceService
     */
    public function setGuichet(\AppBundle\Entity\Guichet $guichet = null)
    {
        $this->guichet = $guichet;

        return $this;
    }

    /**
     * Get guichet
     *
     * @return \AppBundle\Entity\Guichet
     */
    public function getGuichet()
    {
        return $this->guichet;
    }
}
