<?php
namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MoneyQuantityRepository extends EntityRepository
{
	public function findAllByYearOwnerAndDepartment($year, $ownerID, $departmentID)
	{
		$qb	=	$this->getEntityManager()->createQueryBuilder();
		
		$qb
			->select(array('mq'))
			->from('TltAdmnBundle:MoneyQuantity', 'mq')
			->leftJoin('mq.owner', 'o')
			->leftJoin('mq.service', 's')
			->where('mq.year=:year')
			->andwhere('o.id=:owner')
			->andWhere('s.department=:department')
			->setParameter('year', $year)
			->setParameter('owner', $ownerID)
			->setParameter('department', $departmentID);

		try {
			return $qb->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
	}
}