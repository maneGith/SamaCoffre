<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CertificatService
 *
 * @ORM\Table(name="certificat_service")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CertificatServiceRepository")
 */
class CertificatService
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
     * @ORM\ManyToOne(targetEntity="Certificat")
     * 
     * @ORM\JoinColumn(nullable=true)
     */
    private $certificat;
    
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="categorie", type="string", length=255, nullable=true)
     */
    private $categorie;
    
    /**
     * @var int
     *
     * @ORM\Column(name="nbplis", type="integer")
     */
    private $nbplis;
    
    /**
     * @var string
     *
     * @ORM\Column(name="prixunitaire", type="decimal", precision=10,  scale=2)
     * @Assert\Type(type="float")
     */
    private $prixunitaire;


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
     * Set certificat
     *
     * @param \AppBundle\Entity\Certificat $certificat
     *
     * @return CertificatService
     */
    public function setCertificat(\AppBundle\Entity\Certificat $certificat = null)
    {
        $this->certificat = $certificat;

        return $this;
    }

    /**
     * Get certificat
     *
     * @return \AppBundle\Entity\Certificat
     */
    public function getCertificat()
    {
        return $this->certificat;
    }

    

    /**
     * Set prixunitaire
     *
     * @param string $prixunitaire
     *
     * @return CertificatService
     */
    public function setPrixunitaire($prixunitaire)
    {
        $this->prixunitaire = $prixunitaire;

        return $this;
    }

    /**
     * Get prixunitaire
     *
     * @return string
     */
    public function getPrixunitaire()
    {
        return $this->prixunitaire;
    }

   

    /**
     * Set nbplis
     *
     * @param integer $nbplis
     *
     * @return CertificatService
     */
    public function setNbplis($nbplis)
    {
        $this->nbplis = $nbplis;

        return $this;
    }

    /**
     * Get nbplis
     *
     * @return integer
     */
    public function getNbplis()
    {
        return $this->nbplis;
    }

    /**
     * Set categorie
     *
     * @param string $categorie
     *
     * @return CertificatService
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return string
     */
    public function getCategorie()
    {
        return $this->categorie;
    }
}
