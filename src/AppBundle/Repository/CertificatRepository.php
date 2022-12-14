<?php

namespace AppBundle\Repository;

/**
 * CertificatRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CertificatRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByMsAnnee($annee)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT SUBSTRING(c.date, 1, 7) AS periode
                 FROM AppBundle:Certificat c
                 WHERE c.date LIKE :periode
                 GROUP BY periode
                 ORDER BY periode DESC'
            )->setParameter('periode', $annee)
             ->getResult();
    }
    
    public function findByMsAnneeEntreprise($annee, $entreprise)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c.id, c.paiement, SUBSTRING(c.date, 1, 7) AS periode, c.numero, c.montant
                 FROM AppBundle:Certificat c
                 JOIN   c.entreprise e
                 WHERE  c.date LIKE :periode
                 AND    e.id = :entreprise
                 GROUP BY c.id, periode
                 ORDER BY periode DESC'
            )->setParameter('periode', $annee)
             ->setParameter('entreprise', $entreprise)
             ->getResult();
    }
    
    public function findByEntreprisePeriode($entreprise, $periode)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:Certificat c
                 WHERE  c.entreprise = :entreprise
                 AND    c.date LIKE :periode'
            )->setParameter('entreprise', $entreprise)
            ->setParameter('periode', $periode)
             ->getResult();
    }
    
    public function findByEntreprisePeriodeAnterieur($entreprise, $periode)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:Certificat c
                 WHERE  c.entreprise = :entreprise
                 AND    c.date LIKE :periode
                 AND    c.paiement = :paiement'
            )->setParameter('entreprise', $entreprise)
            ->setParameter('periode', $periode)
                 ->setParameter('paiement', 'Non Pay??e')
             ->getResult();
    }
    
    public function findByPeriode($periode)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:Certificat c
                 JOIN   c.entreprise e
                 WHERE  c.date LIKE :periode
                 ORDER BY e.nom ASC'
            )->setParameter('periode', $periode)
             ->getResult();
    }
    
    public function findByPeriodeEntreprise($periode, $entreprise)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:Certificat c
                 JOIN   c.entreprise e
                 WHERE  c.date LIKE :periode
                 AND    e.id = :entreprise
                 ORDER BY e.nom ASC'
            )->setParameter('periode', $periode)
                ->setParameter('entreprise', $entreprise)
             ->getResult();
    }
    
    public function findByNumero($numero)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:Certificat c
                 WHERE  c.numero = :numero'
            )->setParameter('numero', $numero)
             ->getResult();
    }
    
    
}
