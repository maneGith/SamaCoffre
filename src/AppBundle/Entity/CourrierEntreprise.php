<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CourrierEntreprise
 *
 * @ORM\Table(name="courrier_entreprise")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CourrierEntrepriseRepository")
 */
class CourrierEntreprise
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
     * @ORM\ManyToOne(targetEntity="AccesReferenceService")
     * 
     * @ORM\JoinColumn(name="accesreferenceservice", referencedColumnName="id", nullable=true)
     */
    private $accesreferenceservice;
    
    /**
     * @var string
     *
     * @ORM\Column(name="pathPDF",  type="string", length=255)
     */
    private $pathPDF;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nomPDF", type="string", length=255)
     */
    private $nomPDF;
    
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
     * @ORM\Column(name="nature", type="string", length=255, nullable=true)
     */
    private $nature;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="EntrepriseService")
     * 
     * @ORM\JoinColumn(name="entrepriseservice", referencedColumnName="id", nullable=false)
     */
    private $entrepriseservice;
    
    /**
     * @var \Date
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;
    
    /**
     * @var string
     *
     * @ORM\Column(name="profil", type="string", length=255)
     */
    private $profil;
    
    /**
     * @var string
     *
     * @ORM\Column(name="lue", type="string", length=255)
     */
    private $lue;
    
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
     * Set pathPDF
     *
     * @param string $pathPDF
     *
     * @return CourrierEntreprise
     */
    public function setPathPDF($pathPDF)
    {
        $this->pathPDF = $pathPDF;

        return $this;
    }

    /**
     * Get pathPDF
     *
     * @return string
     */
    public function getPathPDF()
    {
        return $this->pathPDF;
    }

    /**
     * Set nomPDF
     *
     * @param string $nomPDF
     *
     * @return CourrierEntreprise
     */
    public function setNomPDF($nomPDF)
    {
        $this->nomPDF = $nomPDF;

        return $this;
    }

    /**
     * Get nomPDF
     *
     * @return string
     */
    public function getNomPDF()
    {
        return $this->nomPDF;
    }

    /**
     * Set volume
     *
     * @param string $volume
     *
     * @return CourrierEntreprise
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return CourrierEntreprise
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
     * Set entrepriseservice
     *
     * @param \AppBundle\Entity\EntrepriseService $entrepriseservice
     *
     * @return CourrierEntreprise
     */
    public function setEntrepriseservice(\AppBundle\Entity\EntrepriseService $entrepriseservice = null)
    {
        $this->entrepriseservice = $entrepriseservice;

        return $this;
    }

    /**
     * Get entrepriseservice
     *
     * @return \AppBundle\Entity\EntrepriseService
     */
    public function getEntrepriseservice()
    {
        return $this->entrepriseservice;
    }

    /**
     * Set profil
     *
     * @param string $profil
     *
     * @return CourrierEntreprise
     */
    public function setProfil($profil)
    {
        $this->profil = $profil;

        return $this;
    }

    /**
     * Get profil
     *
     * @return string
     */
    public function getProfil()
    {
        return $this->profil;
    }

    /**
     * Set accesreferenceservice
     *
     * @param \AppBundle\Entity\AccesReferenceService $accesreferenceservice
     *
     * @return CourrierEntreprise
     */
    public function setAccesreferenceservice(\AppBundle\Entity\AccesReferenceService $accesreferenceservice = null)
    {
        $this->accesreferenceservice = $accesreferenceservice;

        return $this;
    }

    /**
     * Get accesreferenceservice
     *
     * @return \AppBundle\Entity\AccesReferenceService
     */
    public function getAccesreferenceservice()
    {
        return $this->accesreferenceservice;
    }

    

    /**
     * Set lue
     *
     * @param string $lue
     *
     * @return CourrierEntreprise
     */
    public function setLue($lue)
    {
        $this->lue = $lue;

        return $this;
    }

    /**
     * Get lue
     *
     * @return string
     */
    public function getLue()
    {
        return $this->lue;
    }

    /**
     * Set nature
     *
     * @param string $nature
     *
     * @return CourrierEntreprise
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
     * Set page
     *
     * @param string $page
     *
     * @return CourrierEntreprise
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
     * @return CourrierEntreprise
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
