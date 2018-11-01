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
	// public function findAllJoinedToBranchesAndServices($owner, $branch, $location, $department, $service, $userBranches = null)
	public function findAllJoinedToBranchesAndServices($owner, $branch, $zoneLocation, $department, $service, $system, $userBranches = null, $userDepartments = null)
	{
		$qb = $this->getEntityManager()->createQueryBuilder('e');

		$qb->select(
				'eq'
			)
			->from('TltAdmnBundle:Equipment', 'eq')
			->leftJoin('eq.owner', 'o')
			->leftJoin('eq.zoneLocation', 'zl')
			->leftJoin('zl.location', 'lo')
			->leftJoin('zl.branch', 'br')
			->leftJoin('eq.service', 'sv')
			->leftJoin('sv.department', 'dp')
            ->leftJoin('eq.system','system')
			->where('eq.isActive = :isActive')
			->andWhere('zl.branch in (:userBranches)')
			->andWhere('sv.department in (:userDepartments)')
			->setParameter('isActive', true)
			->setParameter('userBranches', $userBranches)
			->setParameter('userDepartments', $userDepartments);
		
		if ($owner)
			$qb->andWhere('eq.owner=:owner')
				->setParameter('owner', $owner);
		
		if ($zoneLocation)
			$qb->andWhere('eq.zoneLocation=:zoneLocation')
				->setParameter('zoneLocation', $zoneLocation);
		elseif ($branch)
			$qb->andWhere('zl.branch=:branch')
					->setParameter('branch', $branch);

		if ($service)
			$qb->andWhere('eq.service=:service')
				->setParameter('service', $service);
		elseif ($department)
			$qb->andWhere('sv.department=:department')
				->setParameter('department', $department);
        if ($system)
            $qb->andWhere('eq.system=:system')
                ->setParameter('system', $system);

        $qb->addOrderBy('eq.name', 'asc');

		try {
			return $qb->getQuery();//->getResult();
		} catch (NoResultException $e) {
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