<?php

namespace AppBundle\Repository;

/**
 * GuichetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GuichetRepository extends \Doctrine\ORM\EntityRepository
{
    public function findGuichetsByAgence($agence)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT g FROM AppBundle:Guichet g
                 JOIN   g.user u
                 WHERE  g.agence = :agence
                 ORDER BY u.nom'
            )->setParameter('agence', $agence)
             ->getResult();
    }
    
      public function findOneByUser($user)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT g FROM AppBundle:Guichet g
                 WHERE  g.user = :user'
            )->setParameter('user', $user)
            ->getResult();
    }

}
