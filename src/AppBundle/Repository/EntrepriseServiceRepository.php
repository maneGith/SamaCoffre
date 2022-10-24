<?php

namespace AppBundle\Repository;

/**
 * EntrepriseServiceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EntrepriseServiceRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByEntreprise($entreprise)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:EntrepriseService e
                 JOIN   e.service t
                 WHERE  e.entreprise = :entreprise
                 ORDER BY t.service'
            )->setParameter('entreprise', $entreprise)
             ->getResult();
    }
    
    public function findByEntrepriseService($entreprise, $service)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:EntrepriseService e
                 WHERE  e.entreprise = :entreprise
                 AND    e.service = :service'
            )->setParameter('entreprise', $entreprise)
                ->setParameter('service', $service)
             ->getResult();
    }
    
    public function findByEntrepriseServiceLabel($entreprise, $service)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:EntrepriseService e
                 JOIN   e.service t
                 WHERE  e.entreprise = :entreprise
                 AND    t.service = :service'
            )->setParameter('entreprise', $entreprise)
                ->setParameter('service', $service)
             ->getResult();
    }
    
      public function findOneById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                 'SELECT e FROM AppBundle:EntrepriseService e
                 WHERE  e.id = :id'
            )->setParameter('id', $id)
            ->getResult();
    }
}