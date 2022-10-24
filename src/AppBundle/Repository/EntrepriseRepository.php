<?php

namespace AppBundle\Repository;

/**
 * EntrepriseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EntrepriseRepository extends \Doctrine\ORM\EntityRepository
{
      public function findOneById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e FROM AppBundle:Entreprise e
                 WHERE  e.id = :id'
            )->setParameter('id', $id)
            ->getResult();
    }
}
