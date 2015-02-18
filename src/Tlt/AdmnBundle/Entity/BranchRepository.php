<?php
namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BranchRepository extends EntityRepository
{
	// public function getAllWithEquipmentsByName()
	// {
		// $queryBuilder = $this->getEntityManager()->createQueryBuilder();
		
		 // $queryBuilder
			// ->select('DISTINCT ac.id, ac.name')
			// ->from('TltAdmnBundle:Branch', 'ac')
			// ->join('l.agencyCenter', 'l')
			// ->join('e.location', 'e');

		// return $queryBuilder;
	// }
	
    public function findAll()
    {
        return $this->findBy(array(), array('name' => 'ASC'));
    }	
	
	public function getAllOfOwner($owner)
	{
		return $this->findBy(
					array(
						'id' => $owner
					)
				);
	}
}