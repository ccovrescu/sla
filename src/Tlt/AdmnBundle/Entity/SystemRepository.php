<?php
namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class SystemRepository extends EntityRepository
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
	
	/**
	 * Intoarce disponibilitatea garanta a unui sistem.
	 */
	public function getGuaranteedDisponibility($system)
	{
		$rsm = new ResultSetMapping();

		$rsm->addScalarResult('guaranteed', 'guaranteed');

		$query = $this->_em->createNativeQuery(
			"SELECT
				value as guaranteed
			FROM
				guaranteed_values gv
			WHERE
				gv.system=?", $rsm
		);   

		$query->setParameter(1, $system);

		try {
			return (float) current($query->getSingleResult());
		} catch (\Doctrine\ORM\NoResultException $e) {
			return 0;
		}
	}
	
	/**
	 * Intoarce numarul total de unitati al unui sistem.
	 */
	public function getGlobalUnitsNo($system)
	{
		$rsm = new ResultSetMapping();

		$rsm->addScalarResult('units', 'units');

		$query = $this->_em->createNativeQuery(
			"SELECT
				SUM(e.total) AS units
			FROM
				mappings m
			LEFT JOIN
				equipments e
				ON e.id=m.equipment
			WHERE
				m.system=?
			GROUP BY
				m.system", $rsm
		);   

		$query->setParameter(1, $system);

		try {
			return (int) current($query->getSingleResult());
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	
	/**
	 * Intoarce suma indisponibilitatilor unui sistem dintr-o perioada de timp.
	 */
	public function getIndisponibleTime($startMoment, $endMoment, $system)
	{
		$rsm = new ResultSetMapping();

		$rsm->addScalarResult('indisponibleTime', 'indisponibleTime');

		$query = $this->_em->createNativeQuery(
			"SELECT
				SUM(tf.resolved_in) AS indisponibleTime
			FROM
				ticket_systems ts
			LEFT JOIN
				ticket_equipments te
				ON te.id=ts.ticket_equipment
			LEFT JOIN
				ticket_create tc
				ON tc.id=te.ticket_create
			LEFT JOIN
				ticket_fix tf
				ON tf.ticket_create=tc.id
			WHERE
				ts.system=?
				AND tf.resolved_at BETWEEN ? AND ?
			GROUP BY
				ts.system", $rsm
		);   

		$query->setParameter(1, $system);
		$query->setParameter(2, $startMoment);
		$query->setParameter(3, $endMoment);
		
		try {
			return (int) current($query->getSingleResult());
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
	
	/**
	 * Calculeaza disponibilitatea unui sistem intr-o perioada de timp.
	 */
	public function getDisponibility($startMoment='2014-07-01 00:00:00', $endMoment = '2014-12-31 23:59:59', $system)
	{
		$start	= new \DateTime($startMoment);
		$end	= new \DateTime($endMoment);
		$interval	=	$start->diff($end);
		
		return round((1 - $this->getIndisponibleTime($startMoment, $endMoment, $system)/($interval->format("%a")*24*60 + $interval->format("%i"))/$this->getGlobalUnitsNo($system))*100, 2);
	}
}