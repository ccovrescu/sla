<?php
namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\EntityRepository;

class EquipmentRepository extends EntityRepository
{
	public function myFindBy($branch = 0, $location = 0, $department = 0, $service = 0)
	{
		if ($location != 0)
			if ($service != 0)
				return $this->findBy(
					array(
						'location' => $location,
						'service' => $service
					));
			elseif ($department != 0)
				return $this->findByService(
							$this->getEntityManager()->getRepository(
									'TltAdmnBundle:Service'
								)
								->findByDepartment($department)
					);
			else
				return $this->findByLocation($location);
		elseif ($branch != 0)
			if ($service != 0)
				return $this->findBy(
					array(
						'location' => $this->getEntityManager()->getRepository(
											'TltAdmnBundle:Location'
										)
										->findByBranch($branch),
						'service' => $service
					));
			elseif ($department != 0)
				return $this->findBy(
					array(
						'location' => $this->getEntityManager()->getRepository(
											'TltAdmnBundle:Location'
										)
										->findByBranch($branch),
						'service' => $this->getEntityManager()->getRepository(
											'TltAdmnBundle:Service'
										)
										->findByDepartment($department)
					));
			else
				return $this->findByLocation(
							$this->getEntityManager()->getRepository(
									'TltAdmnBundle:Location'
								)
								->findByBranch($branch)
						);
		else
			return $this->findAll();
	}
	public function findAllJoinedToBranchesAndServices($owner, $branch, $location, $department, $service)
	{
		// var_dump($branch);
		// var_dump($location);
		// var_dump($department);
		// var_dump($service);
		// die();
		$qb = $this->getEntityManager()->createQueryBuilder();
		
		$qb->select(array('e.id', 'e.name as equipment', 'e.total as total', 'e.inPam as inPam', 'o.name as owner', 'l.name as location', 'ac.name as branch', 's.name as service'))
			->from('TltAdmnBundle:Equipment', 'e')
			->leftJoin('e.owner', 'o')
			->leftJoin('e.location', 'l')
			->leftJoin('l.branch', 'ac')
			->leftJoin('e.service', 's')
			->leftJoin('s.department', 'd');
		
		if ($owner != 0)
			$qb->select()
					->andWhere('e.owner=:owner')
					->setParameter('owner', $owner);
		
		if ($location != 0)
			$qb->select()
					->andWhere('e.location=:location')
					->setParameter('location', $location);
		elseif ($branch != 0)
			$qb->select()
					->andWhere('l.branch=:branch')
					->setParameter('branch', $branch);

		if ($service != 0)
			$qb->select()
					->andWhere('e.service=:service')
					->setParameter('service', $service);
		elseif ($department != 0)
			$qb->select()
					->andWhere('s.department=:department')
					->setParameter('department', $department);		

		try {
			return $qb->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
		
	public function findAllFromOneBranchOrderedByName($branch)
	{
		return $this->findBy(
			array(
				'branch' => $branch
			),
			array(
				'name' => 'ASC'
			)
		);
	}
}