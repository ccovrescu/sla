<?php
namespace Tlt\AdmnBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;

use Tlt\MainBundle\Model\TimeCalculation;

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

    public function findAllFromOneDepartmentOrderedByCategory($department)
    {
        return $this->findBy(
            array(
                'department' => $department
            ),
            array(
                'category' => 'ASC'
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

	/* adaugat la 11.08.2018 */
    /**
     * Intoarce numarul total de unitati ale unui sistem.
     *
     * @param interger $systemId
     *
     * @return interger|null
     */
    public function getGlobalUnitsNo($systemId, $owner_id = null, $start, $end)
    {
        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('units', 'units');

        $query = $this->_em->createNativeQuery(
            "SELECT
				quantity AS units
			FROM
				system_quantity sq
			WHERE
				sq.system = $systemId" .
            ($owner_id != null ? " AND sq.owner=$owner_id " : "").
            " AND sq.start_date<= '".$start."' and sq.end_date>='".$end."'".
            " GROUP BY
				sq.system", $rsm
        );
        //       " AND '".$end."' between sq.start_date and sq.end_date".
        //       " AND ('".$start."' between sq.start_date and sq.end_date) AND ('".$end."' between sq.start_date and sq.end_date)".
        //var_dump($query->getSQL());
        try {
            return (int) current($query->getSingleResult());
        } catch (NoResultException $e) {
            return null;
        }
    }

    /* sfarsit Adaugat la 11.08.2018*/

	/**
	 * Intoarce numarul total de unitati ale unui sistem.
     *
     * @param interger $systemId
     *
     * @return interger|null
	 */
	public function getGlobalUnitsNoBradescu($systemId, $owner_id = null)
	{
		$rsm = new ResultSetMapping();

		$rsm->addScalarResult('units', 'units');

		$query = $this->_em->createNativeQuery(
			"SELECT
				SUM(e.total) AS units
			FROM
				mappings ma
			LEFT JOIN
				equipments e
				ON e.id=ma.equipment
			WHERE
				ma.system = $systemId" .

            ($owner_id != null ? " AND e.owner=$owner_id " : "")

            . "
			GROUP BY
				ma.system", $rsm
		);   

		try {
			return (int) current($query->getSingleResult());
		} catch (NoResultException $e) {
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

//		$query = $this->_em->createNativeQuery(
//			"SELECT
//				SUM(tf.resolved_in) AS indisponibleTime
//			FROM
//				ticket_systems ts
//			LEFT JOIN
//				ticket_equipments te
//				ON te.id=ts.ticket_equipment
//			LEFT JOIN
//				ticket_create tc
//				ON tc.id=te.ticket_create
//			LEFT JOIN
//				ticket_fix tf
//				ON tf.ticket_create=tc.id
//			WHERE
//				ts.system=?
//				AND tf.resolved_at BETWEEN ? AND ?
//			GROUP BY
//				ts.system", $rsm
//		);

        $query = $this->_em->createNativeQuery(
            "
            SELECT
              SUM(ttm.resolved_in) AS indisponibleTime
            FROM
              tickets_ticket_mapping ttm
              INNER JOIN tickets t ON t.id=ttm.ticket_id
              INNER JOIN mappings mp ON mp.id=ttm.mapping_id
            WHERE
              t.is_real=1 AND t.backup_solution=2 # fara solutie de rezerva
              AND
              mp.system=? AND t.fixed_at BETWEEN ? AND ?
            GROUP BY mp.system
            ", $rsm
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
	public function getDisponibility($startMoment='2018-07-01', $endMoment = '2018-12-31', $system)
	{
		$start	= new \DateTime($startMoment);
		$end	= new \DateTime($endMoment);
		$interval	=	$start->diff($end);
        $periodMinutes = $interval->format("%a")*24*60 + $interval->format("%i");

        $periodMinutes = TimeCalculation::getSystemTotalWorkingTime($system->getGuaranteedValues()->first()->getWorkingTime(), $start, $end);

        $indisponibleTime = $this->getIndisponibleTime($startMoment, $endMoment, $system);
        $globalUnitsNo = $this->getGlobalUnitsNo($system);

        $disponibility = round((1 - $indisponibleTime/$periodMinutes/$globalUnitsNo)*100, 2);

		return $disponibility;
	}

    public function SLABradescu($owner, $departments, $start, $end, $isClosed) {

        $departments = implode(',', $departments);

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('criticality', 'criticality');
        $rsm->addScalarResult('min_hour', 'min_hour');
        $rsm->addScalarResult('max_hour', 'max_hour');
        $rsm->addScalarResult('g_disp', 'g_disp');
        $rsm->addScalarResult('indisponible_time', 'indisponible_time');

        $query = $this->_em->createNativeQuery(
            "
                SELECT
                  s.id,
                  s.name,
                  s.criticality,
                  wt.min_hour,
                  wt.max_hour,
                  gv.value AS g_disp,
                  IFNULL(times.indisponible_time,0) AS indisponible_time
                FROM
                  systems s
                  LEFT JOIN
                    (SELECT
                      mp.system AS system_id,
                      SUM(ttm.resolved_in) AS indisponible_time
                    FROM
                      tickets_ticket_mapping ttm
                      LEFT JOIN tickets t
                        ON t.id = ttm.ticket_id
                      LEFT JOIN mappings mp
                        ON mp.id = ttm.mapping_id
                      LEFT JOIN equipments eq
                        ON eq.id=t.equipment_id
                    WHERE t.is_real = 1
                      AND t.backup_solution = 2
                      AND t.fixed_at BETWEEN '$start' AND '$end'
                      " .
//                        ($isClosed == 1 ? ' AND t.is_closed = 1 ' : ' ')
                        ($isClosed == 1 ? ' ' : ' AND t.is_closed = 1 ')
                        . "
                      AND eq.owner = $owner
                    GROUP BY mp.system) times
                    ON times.system_id = s.id
                    LEFT JOIN
                        guaranteed_values gv
                        ON gv.system=s.id
                    LEFT JOIN working_time wt
                        ON wt.id=gv.workingTime
                WHERE s.department IN ($departments)
                ORDER BY s.department,
                  s.name
              ",
            $rsm
        );

     //   var_dump($query->getSQL());

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function SLA($owner, $departments, $start, $end, $isClosed) {
        $end = $end." 23:59:59";
        $departments = implode(',', $departments);

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('criticality', 'criticality');
        $rsm->addScalarResult('min_hour', 'min_hour');
        $rsm->addScalarResult('max_hour', 'max_hour');
        $rsm->addScalarResult('g_disp', 'g_disp');
        $rsm->addScalarResult('indisponible_time_total_affected', 'indisponible_time_total_affected');
        $rsm->addScalarResult('indisponible_time_partial_affected', 'indisponible_time_partial_affected');
        $rsm->addScalarResult('indisponible_time', 'indisponible_time');
        $query = $this->_em->createNativeQuery(
            "
                SELECT
                  s.id,
                  s.name,
                  s.criticality,
                  wt.min_hour,
                  wt.max_hour,
                  gv.value AS g_disp,
                  IFNULL(times.indisponible_time_total_affected,0) AS indisponible_time_total_affected,
                  IFNULL(times.indisponible_time_partial_affected,0) AS indisponible_time_partial_affected,                                    
                  IFNULL(times.indisponible_time,0) AS indisponible_time
                FROM
                  systems s
                  LEFT JOIN
                    (SELECT
                      mp.system AS system_id,
                      SUM(IF(ttm.total_affected, ttm.resolved_in,0)) AS indisponible_time_total_affected,                        
                      SUM(IF(ttm.total_affected,0, ttm.resolved_in)) AS indisponible_time_partial_affected,
                      SUM(ttm.resolved_in) AS indisponible_time          
                    FROM
                      tickets_ticket_mapping ttm
                      LEFT JOIN tickets t
                        ON t.id = ttm.ticket_id
                      LEFT JOIN mappings mp
                        ON mp.id = ttm.mapping_id
                      LEFT JOIN equipments eq
                        ON eq.id=t.equipment_id
                    WHERE t.is_real = 1
                      AND t.backup_solution = 2
                      AND t.fixed_at BETWEEN '$start' AND '$end'
                      " .
//                        ($isClosed == 1 ? ' AND t.is_closed = 1 ' : ' ')
            ($isClosed == 1 ? ' ' : ' AND t.is_closed = 1 ')
            . "
                      AND eq.owner = $owner
                    GROUP BY mp.system) times
                    ON times.system_id = s.id
                    LEFT JOIN
                        guaranteed_values gv
                        ON gv.system=s.id
                    LEFT JOIN working_time wt
                        ON wt.id=gv.workingTime
                WHERE s.department IN ($departments)
                ORDER BY s.department,
                  s.name
              ",
            $rsm
        );

          //var_dump($query->getSQL());

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * Intoarce indisponibilitatile dintr-un sistem anume, pentru toate entitatile care au date.
     *
     * @param null $system_id
     * @param null $start
     * @param null $end
     * @return array|null
     */
    public function getDisponibilitiesForSystem($system_id = null, $start = null, $end = null)
    {
        if ($system_id != null && $start != null && $end != null) {
            $start = $start->format('Y-m-d');
            $end = $end->format('Y-m-d');

            $rsm = new ResultSetMapping();

            $rsm->addScalarResult('owner', 'owner');
            $rsm->addScalarResult('indisponibility', 'indisponibility');

            $query = $this->_em->createNativeQuery(	
                "
                SELECT
                  eq.owner,
                  SUM(ttm.resolved_in) AS indisponibility
                FROM
                  tickets_ticket_mapping ttm
                  LEFT JOIN mappings mp
                    ON mp.id = ttm.mapping_id
                  LEFT JOIN tickets t
                    ON t.id = ttm.ticket_id
                  LEFT JOIN equipments eq
                    ON eq.id = t.equipment_id
                WHERE
                  mp.system = $system_id
                  AND t.is_real=1
                  AND t.backup_solution=2
                  AND t.fixed_at BETWEEN '$start' AND '$end'
                GROUP BY eq.owner
                ",
                $rsm);

            try {
                return $query->getScalarResult();
            } catch (NoResultException $e) {
                return null;
            }
        }

        return null;
    }

    public function findByDepartmentIn($departments)
    {
        $qb = $this->createQueryBuilder('s')
                ->select('s')
                ->where('s.department IN (:departments)')
                ->setParameter('departments', $departments)
            ;

        return $qb->getQuery()->getResult();
    }
}