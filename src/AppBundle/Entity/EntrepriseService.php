<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EntrepriseService
 *
 * @ORM\Table(name="entreprise_service")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EntrepriseServiceRepository")
 */
class EntrepriseService
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
     * @ORM\ManyToOne(targetEntity="Entreprise")
     * 
     * @ORM\JoinColumn(name="entreprise")
     */
    private $entreprise;
    
    /**
     * @ORM\ManyToOne(targetEntity="Tarif")
     * 
     * @ORM\JoinColumn(nullable=true)
     */
    private $service;

    
    
    /**
     * @var string
     *
     * @ORM\Column(name="droitinout", type="string", length=255)
     */
    private $droitinout;
    
    /**
     * @var string
     *
     * @ORM\Column(name="droitguichet", type="string", length=255)
     */
    private $droitguichet;
    
    /**
     * @var string
     *
     * @ORM\Column(name="stockage", type="string", length=255)
     */
    private $stockage;


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
     * Set entreprise
     *
     * @param \AppBundle\Entity\Entreprise $entreprise
     *
     * @return EntrepriseService
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


    /**
     * Set droitinout
     *
     * @param string $droitinout
     *
     * @return EntrepriseService
     */
    public function setDroitinout($droitinout)
    {
        $this->droitinout = $droitinout;

        return $this;
    }

    /**
     * Get droitinout
     *
     * @return string
     */
    public function getDroitinout()
    {
        return $this->droitinout;
    }

    /**
     * Set droitguichet
     *
     * @param string $droitguichet
     *
     * @return EntrepriseService
     */
    public function setDroitguichet($droitguichet)
    {
        $this->droitguichet = $droitguichet;

        return $this;
    }

    /**
     * Get droitguichet
     *
     * @return string
     */
    public function getDroitguichet()
    {
        return $this->droitguichet;
    }

    /**
     * Set service
     *
     * @param \AppBundle\Entity\Tarif $service
     *
     * @return EntrepriseService
     */
    public function setService(\AppBundle\Entity\Tarif $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \AppBundle\Entity\Tarif
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set stockage
     *
     * @param string $stockage
     *
     * @return EntrepriseService
     */
    public function setStockage($stockage)
    {
        $this->stockage = $stockage;

        return $this;
    }

    /**
     * Get stockage
     *
     * @return string
     */
    public function getStockage()
    {
        return $this->stockage;
    }
}
