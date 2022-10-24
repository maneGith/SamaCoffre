<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Certificat
 *
 * @ORM\Table(name="certificat")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CertificatRepository")
 */
class Certificat
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
     * @var \Date
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;
    
    /**
     * @ORM\ManyToOne(targetEntity="Entreprise")
     * 
     * @ORM\JoinColumn(name="entreprise")
     */
    private $entreprise;
    
    /**
     * @var string
     *
     * @ORM\Column(name="paiement", type="string", length=255)
     */
    private $paiement;
    
    /**
     * @var string
     *
     * @ORM\Column(name="montant", type="string", length=255, nullable=true)
     */
    private $montant;
    
    /**
     * @var string
     *
     * @ORM\Column(name="horstva", type="string", length=255, nullable=true)
     */
    private $horstva;
    
    /**
     * @var string
     *
     * @ORM\Column(name="tva", type="string", length=255, nullable=true)
     */
    private $tva;

    
    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=255, nullable=true)
     */
    private $numero;
    
    /**
     * @var \Date
     *
     * @ORM\Column(name="datepaie", type="date", nullable=true)
     */
    private $datepaie;


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Certificat
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
     * Set paiement
     *
     * @param string $paiement
     *
     * @return Certificat
     */
    public function setPaiement($paiement)
    {
        $this->paiement = $paiement;

        return $this;
    }

    /**
     * Get paiement
     *
     * @return string
     */
    public function getPaiement()
    {
        return $this->paiement;
    }

    /**
     * Set entreprise
     *
     * @param \AppBundle\Entity\Entreprise $entreprise
     *
     * @return Certificat
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
     * Set numero
     *
     * @param string $numero
     *
     * @return Certificat
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set montant
     *
     * @param string $montant
     *
     * @return Certificat
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return string
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set horstva
     *
     * @param string $horstva
     *
     * @return Certificat
     */
    public function setHorstva($horstva)
    {
        $this->horstva = $horstva;

        return $this;
    }

    /**
     * Get horstva
     *
     * @return string
     */
    public function getHorstva()
    {
        return $this->horstva;
    }

    /**
     * Set tva
     *
     * @param string $tva
     *
     * @return Certificat
     */
    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * Get tva
     *
     * @return string
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set datepaie
     *
     * @param \DateTime $datepaie
     *
     * @return Certificat
     */
    public function setDatepaie($datepaie)
    {
        $this->datepaie = $datepaie;

        return $this;
    }

    /**
     * Get datepaie
     *
     * @return \DateTime
     */
    public function getDatepaie()
    {
        return $this->datepaie;
    }
}
