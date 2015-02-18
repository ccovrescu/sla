<?php
namespace Tlt\TicketBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class TicketFixRepository extends EntityRepository
{
	public function getAffectedSystems($owner)
	{
		$rsm = new ResultSetMapping();

		$rsm->addScalarResult('owner', 'owner');
		$rsm->addScalarResult('service', 'service');
		$rsm->addScalarResult('system', 'system');
		$rsm->addScalarResult('equipment', 'equipment');

		$query = $this->_em->createNativeQuery(
			"SELECT
				eq.owner,
				eq.service,
				ts.`system`,
				te.`equipment`,
				COUNT(eq.id) AS numar
			FROM
				ticket_systems ts
			LEFT JOIN
				ticket_equipments te
				ON te.id=ts.ticket_equipment
			LEFT JOIN
				equipments eq
				ON eq.id=te.equipment
			LEFT JOIN
				ticket_create tc
				ON tc.id=te.ticket_create
			JOIN
				ticket_fix tf
				ON tf.ticket_create=tc.id
			WHERE
				eq.owner=?
			GROUP BY
				ts.system,
				eq.service,
				eq.owner
			ORDER BY
				OWNER,
				service,
				system,
				equipment", $rsm
		);   

		$query->setParameter(1, $owner);

		try {
			return $query->getArrayResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
}