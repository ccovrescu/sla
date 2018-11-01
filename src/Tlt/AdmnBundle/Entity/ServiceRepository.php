<?php
namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class ServiceRepository extends EntityRepository
{
	public function findAllOrderedByName()
	{
		return $this->findBy(array(), array('department' => 'ASC', 'name' => 'ASC'));
	}
	
	public function findAllFromOneDepartmentOrderedByName($department)
	{
		return $this->findBy(
			array(
				'department' => $department
			),
			array(
				'name' => 'ASC'
			)
		);
	}


	public function getServiceUnitsNo($service)
	{
		$rsm = new ResultSetMapping();

		$rsm->addScalarResult('system', 'system');
		$rsm->addScalarResult('units', 'units');

		$query = $this->_em->createNativeQuery(
			"SELECT
				m.system,
				SUM(e.total) AS units
			FROM
				mappings m
			LEFT JOIN
				equipments e
				ON e.id=m.equipment
			WHERE
				e.service=?
			GROUP BY
				m.system", $rsm
		);   

		$query->setParameter(1, $service);

		try {
			$values	=	array();
			$total	=	0;
			
			$rows	=	$query->getResult();
			foreach($rows as $row)
			{
				$values[$row['system']]	=	(int) $row['units'];
				$total	+=	(int) $row['units'];
			}
			$values['total']	=	$total;
			
			return $values;
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
	
	public function getServiceValue($year, $owner, $service)
	{
		$rsm = new ResultSetMapping();

		$rsm->addScalarResult('value', 'value');

		$query = $this->_em->createNativeQuery(
			"SELECT
				mq.quantity*mp.price AS value
			FROM
				money_quantities mq
			LEFT JOIN
				money_price mp
				ON mp.year=mq.year
				AND mp.service=mq.service
			WHERE
				mq.year=?
				AND mq.owner=?
				AND mq.service=?", $rsm
		);   

		$query->setParameter(1, $year);
		$query->setParameter(2, $owner);
		$query->setParameter(3, $service);

		try {
			return (float) current($query->getSingleResult());
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
}