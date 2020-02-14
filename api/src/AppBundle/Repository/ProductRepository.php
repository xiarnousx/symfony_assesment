<?php

namespace AppBundle\Repository;

use AppBundle\Repository\Contract\SqlCountable;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends \Doctrine\ORM\EntityRepository implements SqlCountable
{
    public function total($criteria = []): int
    {
        $queryBuilder = $this->createQueryBuilder('p');
        return $queryBuilder->select('count(p.id)')->getQuery()->getSingleScalarResult();
    }
}
