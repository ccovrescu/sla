<?php
namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LocationRepository extends EntityRepository
{
	public function findAllOrderedByName()
	{
		return $this->findBy(array(), array('branch' => 'ASC', 'name' => 'ASC'));
	}
	
	public function findAllFromOneAgencyCenterOrderedByName($branch)
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