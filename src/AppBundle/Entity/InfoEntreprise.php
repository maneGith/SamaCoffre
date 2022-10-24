<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfoEntreprise
 *
 * @ORM\Table(name="info_entreprise")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InfoEntrepriseRepository")
 */
class InfoEntreprise
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
     * @ORM\Column(name="nature", type="string", length=255)
     */
    private $nature;

    /**
     * @var string
     *
     * @ORM\Column(name="objet", type="string", length=255)
     */
    private $objet;
    
    
    /**
     * @ORM\Column(type="string")
     */
    private $brochureFilename;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;
    
    /**
     * @ORM\ManyToOne(targetEntity="Entreprise")
     * 
     * @ORM\JoinColumn(nullable=true)
     */
    private $entreprise;
    
    /**
     * @var string
     *
     * @ORM\Column(name="volume", type="string", length=255)
     */
    private $volume;
    
    /**
     * @var string
     *
     * @ORM\Column(name="page", type="string", length=255)
     */
    private $page;
    
    /**
     * @var string
     *
     * @ORM\Column(name="essai", type="string", length=255, nullable=true)
     */
    private $essai;
    
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
     * Set nature
     *
     * @param string $nature
     *
     * @return InfoEntreprise
     */
    public function setNature($nature)
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * Get nature
     *
     * @return string
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * Set objet
     *
     * @param string $objet
     *
     * @return InfoEntreprise
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet
     *
     * @return string
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set brochureFilename
     *
     * @param string $brochureFilename
     *
     * @return InfoEntreprise
     */
    public function setBrochureFilename($brochureFilename)
    {
        $this->brochureFilename = $brochureFilename;

        return $this;
    }

    /**
     * Get brochureFilename
     *
     * @return string
     */
    public function getBrochureFilename()
    {
        return $this->brochureFilename;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return InfoEntreprise
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set entreprise
     *
     * @param \AppBundle\Entity\Entreprise $entreprise
     *
     * @return InfoEntreprise
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
     * Set volume
     *
     * @param string $volume
     *
     * @return InfoEntreprise
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return string
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set page
     *
     * @param string $page
     *
     * @return InfoEntreprise
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set essai
     *
     * @param string $essai
     *
     * @return InfoEntreprise
     */
    public function setEssai($essai)
    {
        $this->essai = $essai;

        return $this;
    }

    /**
     * Get essai
     *
     * @return string
     */
    public function getEssai()
    {
        return $this->essai;
    }
}
