<?php

namespace AppBundle\Repository;

/**
 * GastoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GastoRepository extends \Doctrine\ORM\EntityRepository
{

	/**
	 *
	 * @return array
	 */
	public function findLastId()
	{

			$qb = $this->createQueryBuilder('g');
			$qb->setMaxResults(1);
			$qb->orderBy('g.id', 'DESC');

			return $qb->getQuery()->getOneOrNullResult();		
	}

}
