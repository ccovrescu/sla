<?php
namespace Tlt\MainBundle\Model;

class SQL
{
	/**
	 * Intoarece lista sistemelor permise spre selectie, in cadrul serviciilor, ordonate dupa
	 * servicii, departamente, entitati.
	 */
	// public static function getAllowedSystems()
	// {
		// $query = "
			// SELECT
				// ow.`id` AS `owner`,
				// sv.`department` AS department,
				// sv.`id` AS `service`,
				// sts.`system`
			// FROM
				// owners ow,
				// services sv
			// LEFT JOIN
				// service_to_systems sts
				// ON sts.`service`=sv.`id`
			// ORDER BY
				// ow.id,
				// sv.id,
				// sts.`system`		
		// ";
		
		// return $query;
	// }
	
	/**
	 * Intoarce lista sistemelor afectate si timpul de rezolvare.
	 * FARA a face grupari.
	 */
	public static function getAffectedSystemsResolvedTimes()
	{
		$query = "
			SELECT
				eq.`owner`,
				eq.`service`,
				ts.`system`,
				TIMESTAMPDIFF(MINUTE, tc.`occured_at`, tf.`resolved_at`) AS resolved_in
			FROM
				ticket_fix tf
			JOIN
				ticket_create tc
				ON tc.`id`=tf.`ticket_create`
			JOIN
				ticket_equipments te
				ON te.`ticket_create`=tf.`ticket_create`
			JOIN
				ticket_systems ts
				ON ts.`ticket_equipment`=te.`id`
			JOIN
				equipments eq
				ON eq.id=te.`equipment`
			WHERE
				tf.`is_real` AND
				tc.`occured_at` BETWEEN :start AND :end
			ORDER BY
				eq.`owner`,
				eq.`service`,
				ts.`system`
		";
		
		return $query;
	}
	// public static function resolvedTickets()
	// {
		// return $sql = "
			// SELECT
				// tf.ticket_create,
				// tc.`occured_at`,
				// tf.`resolved_at`,
				// te.`equipment`,
				// eq.owner,
				// sv.`department`,
				// sv.id AS `service`,
				// ts.`system`,
				// cr.`priority`,
				// IF(lo.`near`, cr.`near_target`, cr.`far_target`) AS target,
				// TIMESTAMPDIFF(MINUTE, tc.`occured_at`, tf.`resolved_at`) AS resolved_in
			// FROM
				// ticket_fix tf
			// LEFT JOIN
				// ticket_create tc
				// ON tc.id=tf.`ticket_create`
			// LEFT JOIN
				// ticket_equipments te
				// ON te.`ticket_create`=tf.`ticket_create`
			// LEFT JOIN
				// ticket_systems ts
				// ON ts.`ticket_equipment`=te.`id`
			// LEFT JOIN
				// systems sy
				// ON sy.`id`=ts.`system`
			// LEFT JOIN
				// criticalities cr
				// ON cr.id=sy.`criticality`
			// LEFT JOIN
				// equipments eq
				// ON eq.id=te.`equipment`
			// LEFT JOIN
				// locations lo
				// ON lo.id=eq.`location`
			// LEFT JOIN
				// services sv
				// ON sv.`id`=eq.`service`
			// WHERE
				// tf.`is_real`";
	// }
	
	// public static function getEquipmentsAndUsersNumber()
	// {
		// $sql = "
			// SELECT
				// eq.`owner`,
				// sv.`department`,
				// sts.`service`,
				// sts.`system`,
				// COUNT(*) AS nr
			// FROM
				// service_to_systems sts
			// JOIN
				// mappings mp
				// ON mp.`system`=sts.`system`
			// JOIN
				// equipments eq
				// ON eq.`id`=mp.`equipment` AND eq.`service`=sts.`service`
			// JOIN
				// services sv
				// ON sv.id=sts.`service`
			// GROUP BY `system`, `service`, `department`, `owner`
			// ORDER BY `owner`, `department`, `service`, `system`
		// ";
	// }
	
	// public static function getDisponibility($ownerID = 0, $departmentID = 0, $serviceID = 0)
	// {
		// $sql = "
			// SELECT
				// eq.`owner`,
				// sv.`department`,
				// sts.`service`,
				// sts.`system`,
				// sys.`name`,
				// (1-SUM(IF(t.`resolved_in`, t.`resolved_in`, 0))/TIMESTAMPDIFF(MINUTE, '2014-07-01 00:00:00', '2014-12-31 23:59:59'))*100 AS `disponibility`
			// FROM
				// service_to_systems sts
			// JOIN
				// systems sys
				// ON sys.`id`=sts.`system`
			// JOIN
				// mappings mp
				// ON mp.`system`=sts.`system`
			// JOIN
				// equipments eq
				// ON eq.`id`=mp.`equipment` AND eq.`service`=sts.`service`
			// JOIN
				// services sv
				// ON sv.id=sts.`service`
			// LEFT JOIN (
				// SELECT
					// ts.`system`,
					// te.`equipment`,
					// tc.`occured_at`,
					// tf.`resolved_at`,
					// TIMESTAMPDIFF(MINUTE, tc.`occured_at`, tf.`resolved_at`) AS resolved_in
				// FROM
					// ticket_fix tf
				// LEFT JOIN
					// ticket_create tc
					// ON tc.id=tf.`ticket_create`
				// LEFT JOIN
					// ticket_equipments te
					// ON te.`ticket_create`=tf.`ticket_create`
				// LEFT JOIN
					// ticket_systems ts
					// ON ts.`ticket_equipment`=te.id
				// WHERE
					// tf.`is_real`
			// ) AS t
			// ON t.system=sts.system AND t.equipment=mp.equipment";
			
			
			// /**
			 // * WHERE
			 // */
			// if ($ownerID != 0) {	# O entitate anume.
				// $sql .= " WHERE eq.`owner`=:owner";
				
				// if ($departmentID !=0) {	# Un departament anume.
					// $sql .= " AND sv.`department`=:department";
					
					// if ($serviceID != 0) {	# Un serviciu anume.
						// $sql .= " AND sts.`service`=:service";
					// } else {	# Toate serviciile din departament.
					// }
				// } else {	# Toate departamentele din entitate.
				// }
			// }	# Toate entitatile.
			
			
			// /**
			 // * GROUP BY 
			 // */
			// $sql .= " GROUP BY `system`, `service`, `department`, `owner`";
			
			// /**
			 // * ORDER BY
			 // */
			// $sql .= " ORDER BY `owner`, `department`, `service`, `system`";
		// return $sql;
	// }

	public static function getSystemsDisponibility()
	{
		$query = "
			SELECT
				sys.id,
				sys.name,
				IF (gv.`value`, gv.`value`, 0) AS guaranteed,
				IF (t.disponibility, t.disponibility,100.00) AS disponibility
			FROM
				systems sys
			LEFT JOIN
				guaranteed_values gv
				ON gv.`system`=sys.`id`
				AND gv.`min_hour`='07:30:00'
				AND gv.`max_hour`='16:30:00'
			LEFT JOIN
				(
				SELECT
					ts.`system`,
					ROUND((1-SUM(IF(TIMESTAMPDIFF(MINUTE, tc.`occured_at`, tf.`resolved_at`), TIMESTAMPDIFF(MINUTE, tc.`occured_at`, tf.`resolved_at`), 0))/TIMESTAMPDIFF(MINUTE, '2014-07-01 00:00:00', '2014-12-31 23:59:59')/t1.total)*100,2) AS `disponibility`
					FROM
						ticket_fix tf
					LEFT JOIN
						ticket_create tc
						ON tc.id=tf.`ticket_create`
					LEFT JOIN
						ticket_equipments te
						ON te.`ticket_create`=tf.`ticket_create`
					LEFT JOIN
						ticket_systems ts
						ON ts.`ticket_equipment`=te.id
					LEFT JOIN (
						" . self::getCurrentNumberOfEquipmentsAndUsers('system') . "
					) AS t1
					ON t1.system=ts.system
					WHERE
						tf.`is_real`
					GROUP BY
						ts.`system`
					ORDER BY
						ts.`system`
				) AS t
				ON t.system=sys.id
		";
		
		return $query;
	}
	
	public static function getSLA()
	{
		$query = "
			SELECT
				t1.owner,
				t1.department,
				t1.service,
				srv.`name`,
				mp.price,
				t2.nr AS db_nr,
				t4.quantity,
				t3.disponibility
			FROM
				# lista tuturor entitatilor, departamentelor si serviciilor
				(
					SELECT
						owners.id AS `owner`,
						services.`department` AS `department`,
						services.id AS `service`
					FROM
						owners,
						services
					ORDER BY
						owners.id, services.`department`, services.`id`
				) AS t1
			LEFT JOIN
				# numarul echipamentelor/utilizatorilor de sisteme/aplicatie
				(
					SELECT
						eq.`owner`,
						sv.`department`,
						sts.`service`,
						sts.`system`,
						COUNT(*) AS nr
					FROM
						service_to_systems sts
					JOIN
						mappings mp
						ON mp.`system`=sts.`system`
					JOIN
						equipments eq
						ON eq.`id`=mp.`equipment` AND eq.`service`=sts.`service`
					JOIN
						services sv
						ON sv.id=sts.`service`
					GROUP BY
						sts.`service`, sv.`department`, eq.`owner`
				) AS t2
				ON t2.owner=t1.owner AND t2.department=t1.department AND t2.service=t1.service
			LEFT JOIN 
				# disponibilitatile pe servicii
				(
					SELECT
						t1.`owner`,
						t1.department,
						t1.`service`,
						ROUND((1-SUM(IF(t2.`resolved_in`, t2.`resolved_in`, 0))/TIMESTAMPDIFF(MINUTE, '2014-07-01 00:00:00', '2014-12-31 23:59:59'))*100,2) AS `disponibility`
					FROM
						(
							SELECT
								ow.`id` AS `owner`,
								sv.`department` AS department,
								sv.`id` AS `service`
							FROM
								owners ow,
								services sv
							ORDER BY
								ow.id, sv.id
						) AS t1
					LEFT JOIN
						(
							SELECT
								eq.`owner`,
								eq.`service`,
								te.`equipment`,
								tc.`occured_at`,
								tf.`resolved_at`,
								TIMESTAMPDIFF(MINUTE, tc.`occured_at`, tf.`resolved_at`) AS resolved_in
							FROM
								ticket_fix tf
							LEFT JOIN
								ticket_create tc
								ON tc.id=tf.`ticket_create`
							LEFT JOIN
								ticket_equipments te
								ON te.`ticket_create`=tf.`ticket_create`
							LEFT JOIN
								equipments eq
								ON eq.`id`=te.`equipment`
							WHERE
								tf.`is_real`
							ORDER BY
								eq.`owner`, eq.`service`
						) AS t2
						ON (t2.`owner`=t1.`owner` AND t2.`service`=t1.`service`)
					GROUP BY
						t1.service, t1.department, t1.owner
					ORDER BY
						t1.owner, t1.department, t1.service
				) AS t3
				ON t3.owner=t1.owner AND t3.department=t1.department AND t3.service=t1.service
			LEFT JOIN
				# pretul unitar al serviciului respectiv
				money_price mp
				ON mp.`year`=2014 AND mp.`service`=t1.service
			LEFT JOIN
				# lista cantitatilor contractate
				(
					SELECT
						mq.`owner`,
						sv.`department`,
						mq.`service`,
						mq.`quantity`
					FROM
						money_quantities mq
					LEFT JOIN
						services sv
						ON sv.id=mq.`service`
					WHERE
						mq.year=2014
					ORDER BY
						mq.`owner`, sv.`department`, mq.`service`
				) AS t4
				ON t4.owner=t1.owner AND t4.department=t1.department AND t4.service=t1.service
	LEFT JOIN
				services srv
				ON srv.id=t1.service
		";
		
		$query .= " WHERE t1.owner=:owner AND t1.department=:department";
		
		return $query;
	}

	/**
	 * Intoarce lista tuturor serviciilor cu timpul de rezolvare.
	 * FARA a lua in considerare sistemele din care este compus.
	 * 
	 * Coloane: (owner, department, service, resovled_in)
	 */
	// public static function getResolvedTimeOfAllServicesWithoutSystems()
	// {
		// $query = "
			// SELECT
				// t1.`owner`,
				// t1.`department`,
				// t1.`service`,
				// t2.resolved_in
			// FROM
				// # lista serviciilor pentru fiecare entitate in parte,
				// # ordonate dupa entitate, departament si serviciu.
				// (
					// SELECT
						// ow.`id` AS `owner`,
						// sv.`department` AS department,
						// sv.`id` AS `service`
					// FROM
						// owners ow,
						// services sv
					// ORDER BY
						// ow.id,
						// sv.id
				// ) AS t1
			// LEFT JOIN
				// # lista serviciilor pentru care au aparut deranjamente si timpul de rezolvare,
				// # ordonate dupa entitate, departament si serviciu
				// (
					// SELECT
						// eq.`owner`,
						// eq.`service`,
						// TIMESTAMPDIFF(MINUTE, tc.`occured_at`, tf.`resolved_at`) AS resolved_in
					// FROM
						// ticket_fix tf
					// LEFT JOIN
						// ticket_create tc
						// ON tc.id=tf.`ticket_create`
					// LEFT JOIN
						// ticket_equipments te
						// ON te.`ticket_create`=tf.`ticket_create`
					// LEFT JOIN
						// equipments eq
						// ON eq.`id`=te.`equipment`
					// WHERE
						// tf.`is_real`
					// ORDER BY
						// eq.`owner`,
						// eq.`service`
				// ) AS t2
				// ON t2.`owner`=t1.`owner` AND t2.`service`=t1.`service`
		// ";
		
		// return $query;
	// }
	
	
	/**
	 * Intoarce lista tuturor serviciilor cu timpul de rezolvare.
	 * LUAND in considerare sistemele din care este compus.
	 * 
	 * Coloane: (owner, department, service, system, resovled_in)
	 */
	// public static function getResolvedTimeOfAllServicesWithSystems()
	// {
		// $query = "
			// SELECT
				// t1.`owner`,
				// t1.`department`,
				// t1.`service`,
				// t1.`system`,
				// t2.resolved_in
			// FROM"
				 // lista serviciilor cu sisteme pentru fiecare entitate in parte,
				 // ordonate dupa entitate, departament, serviciu si sistem.
				// . "(
					// " . self::getAllowedSystems() . "
				// ) AS t1
			// LEFT JOIN"
				 // lista serviciilor cu sisteme pentru care au aparut deranjamente si timpul de rezolvare,
				 // ordonate dupa entitate, departament, serviciu	si sistem
				// . "(
					// " . self::getAffectedSystemsResolvedTimes() . "
				// ) AS t2
				// ON t2.`owner`=t1.`owner` AND t2.`service`=t1.`service`
		// ";
		
		// return $query;
	// }
	
	/**
	 * ####
	 */
	// public static function getServicesDisponibility($ownerID = 0, $departmentID = 0)
	// {
		// $sql = "
			// SELECT
				// t.`owner`,
				// t.`department`,
				// t.`service`,
				// srv.`name`,
				// ROUND((1-SUM(IF(t.`resolved_in`, t.`resolved_in`, 0))/TIMESTAMPDIFF(MINUTE, '2014-07-01 00:00:00', '2014-12-31 23:59:59'))*100,2) AS `disponibility`
			// FROM
				// (" . SQL::getResolvedTimeOfAllServicesWithoutSystems() . "
				// ) AS t
			// LEFT JOIN
				// services srv
				// ON srv.`id`=t.`service`
			// ";

		// /**
		 // * WHERE
		 // */
		// if ($ownerID != 0)	# O entitate anume.
			// $sql .= " WHERE t.`owner`=:owner AND t.`department`=:department";
		// else	# Toate entitatile.
			// $sql .= " WHERE t.`department`=:department";
			
			
		// /**
		 // * GROUP BY 
		 // */
		// if ($ownerID != 0)
			// $sql .= " GROUP BY t.`service`, t.`department`";
		// else
			// $sql .= " GROUP BY t.`service`";
			
		// /**
		 // * ORDER BY
		 // */
		// $sql .= " ORDER BY t.`owner`, t.`department`, t.`service`";		
		
		// return $sql;
	// }

	public static function getSystemsDisponibilityByService()
	{
		$query = "
			SELECT
				t.service,
				t.system,
				sys.name,
				COUNT(*) AS total,
				SUM(IF(t.resolved_in, t.resolved_in, 0)) AS indisponible_time,
				TIMESTAMPDIFF(MINUTE, :start, :end) AS total_time,
				ROUND((1-SUM(IF(t.resolved_in, t.resolved_in, 0))/TIMESTAMPDIFF(MINUTE, :start, :end)/u.total)*100,2) AS disponibility
			FROM
				(
					/* toate echipamentele/utilizatorii/aplicatiile cu timpul de rezolvare unde este cazul */
					SELECT
						t1.equipment,
						t1.service,
						t1.system,
						t2.resolved_in
					FROM
						(
							/* toate maparile dintre servicii si sisteme */
							SELECT
								mp.`equipment`,
								eq.`service`,
								mp.`system`
							FROM
								mappings mp
							LEFT JOIN
								equipments eq
								ON eq.`id`=mp.`equipment`
							ORDER BY
								mp.`equipment`, eq.`service`, mp.`system`
						) AS t1
					LEFT JOIN
						(
							/* lista sistemelor afectate cu timpul de rezolvare */
							SELECT
								te.`equipment`,
								eq.`service`,
								ts.`system`,
								TIMESTAMPDIFF(MINUTE, tc.`occured_at`, tf.`resolved_at`) AS resolved_in
							FROM
								ticket_systems ts
							LEFT JOIN
								ticket_equipments te
								ON te.`id`=ts.`ticket_equipment`
							LEFT JOIN
								ticket_create tc
								ON tc.`id`=te.`ticket_create`
							LEFT JOIN
								ticket_fix tf
								ON tf.`ticket_create`=tc.`id`
							LEFT JOIN
								equipments eq
								ON eq.`id`=te.`equipment`
							WHERE
								tf.`is_real` AND tc.`occured_at` BETWEEN :start AND :end
							ORDER BY
								te.`equipment`, eq.`service`, ts.`system`
						) AS t2
						ON t2.equipment=t1.equipment AND t2.service=t1.service AND t2.system=t1.system
				) AS t
			LEFT JOIN
				(
					" . self::getCurrentNumberOfEquipmentsAndUsers('service') . "
				) AS u
				ON u.service=t.service AND u.system=t.system
			LEFT JOIN
				systems sys
				ON sys.id=t.system
			WHERE
				t.service=:service
			GROUP BY
				t.system, t.service
			ORDER BY
				t.service, t.system
	";
		
		return $query;
	}
	
	/**
	 * Numarul total de echipamente si utilizatori de aplicatii/sisteme.
	 * In functie de $groupBySystem intoarce rezultatul grupat dupa:
	 * - sisteme, servicii, departamente, entitati
	 * - sau doar dupa servicii, departamente, entitati.
	 */
	public static function getCurrentNumberOfEquipmentsAndUsers($groupBy)
	{
		$query = "
			SELECT
				eq.`owner`,
				sv.`department`,
				eq.`service`,
				mp.`system`,
				SUM(eq.total) AS total
			FROM
				mappings mp
			LEFT JOIN
				equipments eq
				ON eq.`id`=mp.`equipment`
			LEFT JOIN
				services sv
				ON sv.`id`=eq.`service`";
				
		switch ($groupBy) {
			case 'systems':
				$query .= "
					GROUP BY
						mp.`system`,
						eq.`service`,
						sv.`department`,
						eq.`owner`
					ORDER BY
						eq.`owner`,
						sv.`department`,
						eq.`service`,
						mp.`system`
					";
				break;
			case 'services':
				$query .= "
					GROUP BY
						eq.`service`,
						sv.`department`,
						eq.`owner`
					ORDER BY
						eq.`owner`,
						sv.`department`,
						eq.`service`
					";
				break;
			case 'system':
				$query .= "
					GROUP BY
						mp.`system`
					ORDER BY
						mp.`system`
					";
				break;
			case 'service':
				$query .= "
					GROUP BY
						mp.`system`,
						eq.`service`
					ORDER BY
						eq.`service`,
						mp.`system`
					";
				break;
		}
		
		return $query;
	}

	/**
	 * Returns affected systems with resolved times in minutes.
	 */
	public static function getSystemWithResolvedTimes()
	{
		$query = "
			SELECT
				eq.`owner`,
				sv.`department`,
				eq.`service`,
				ts.`system`,
				IF(gv.`value`, gv.`value`, 0) AS guaranteed_disponibility,
				TIMESTAMPDIFF(MINUTE, tc.`occured_at`, tf.`resolved_at`) AS resolved_in
			FROM
				ticket_systems ts
			JOIN
				ticket_equipments te
				ON te.`id`=ts.`ticket_equipment`
			JOIN
				ticket_create tc
				ON tc.id=te.`ticket_create`
			JOIN
				ticket_fix tf
				ON tf.`ticket_create`=tc.`id`
			LEFT JOIN
				guaranteed_values gv
				ON gv.`system`=ts.`system` AND DATE_FORMAT(tc.`occured_at`,'%H:%i:%s') BETWEEN gv.`min_hour` AND gv.`max_hour`
			JOIN equipments eq
				ON eq.`id`=te.`equipment`
			JOIN
				services sv
				ON sv.id=eq.`service`
			WHERE
				tc.`occured_at` BETWEEN :start AND :end
			ORDER BY
				eq.`owner`,
				sv.`department`,
				eq.`service`,
				ts.`system`
		";
		
		return $query;
	}
	
	/**
	 * Din Anexa A, pentru fiecare serviciu, intoarce pretul unitar si cantitatea.
	 */
	public static function getContractUnitPricesAndQuantities()
	{
		$query = "
			SELECT
				mq.`owner`,
				mq.`service`,
				mp.`price`,
				mq.`quantity`
			FROM
				money_quantities mq
			JOIN
				money_price mp
				ON mp.`service`=mq.`service` AND mp.`year`=mq.`year`
			WHERE
				mq.`year`=:year
			ORDER BY
				mq.`owner`,
				mq.`service`		
		";
		
		return $query;
	}

	/**
	 * Conform Anexa A, intoarce pretul si cantitatile din contract, unite cu preturile si cantitatile
	 * rezultate prin impartirea valorii contractuale la numarul de echipamente si utilizatori de
	 * aplicatie/sistem gasiti in tabela de mapari.
	 */
	public static function getCalculatedUnitPricesAndQuantities()
	{
		$query = "
			SELECT
				t1.owner,
				t1.service,
				t1.quantity AS contract_quantity,
				t1.price AS contract_price,
				t2.total AS calculated_quantity,
				ROUND(t1.price*t1.quantity/t2.total,2) AS calculated_price
			FROM
				(
					" . SQL::getContractUnitPricesAndQuantities() . "
				) AS t1
			LEFT JOIN
				(
					" . SQL::getCurrentNumberOfEquipmentsAndUsers('services') . "
				) AS t2
				ON t2.`owner`=t1.`owner` AND t2.`service`=t1.`service`
			ORDER BY
				t1.`owner`, t1.`service`
		";
		
		return $query;
	}
	
	/**
	 * Intoarce disponibilitatea garantata si cea calculata, doar pentru sistemele afectate.
	 */
	public static function getDisponibilityForAffectedSystems()
	{
		$query = "
			SELECT
				  t1.owner,
				  t1.service,
				  t1.system,
				  t3.total as calculated_system_quantity,
				  t4.total as altceva,
				  t2.calculated_price,
				  t1.guaranteed_disponibility,
				  ROUND((1-SUM(IF(t1.`resolved_in`, t1.`resolved_in`, 0))/TIMESTAMPDIFF(MINUTE, :start, :end)/t3.total)*100,2) AS `calculated_disponibility`  
			FROM
				(
					" . self::getSystemWithResolvedTimes() . "
				) AS t1
			JOIN
				(
					" . self::getCalculatedUnitPricesAndQuantities() . "
				) AS t2
				ON t2.`owner`=t1.`owner` AND t2.`service`=t1.`service`
			JOIN
				(
					" . self::getCurrentNumberOfEquipmentsAndUsers('systems') . "
				) AS t3
				ON t3.`owner`=t1.`owner` AND t3.`service`=t1.`service` AND t3.`system`=t1.`system`
			LEFT JOIN
				(
					" . self::getCurrentNumberOfEquipmentsAndUsers('system') . "
				) AS t4
			ON t4.system=t1.system
			GROUP BY
					t1.system,
					t1.service,
					t1.owner
		";
		
		return $query;
	}
	
	public static function getAffectedSystemPenalities()
	{
		$query = "
			SELECT
				t1.*,
				ROUND(IF(t1.calculated_disponibility/t1.guaranteed_disponibility < 0.9, 0.1, t1.calculated_disponibility/t1.guaranteed_disponibility)*t1.calculated_system_quantity*t1.calculated_price,2) AS penality
			FROM
				(
					" . self::getDisponibilityForAffectedSystems() . "
				) AS t1
			WHERE
				t1.calculated_disponibility<t1.guaranteed_disponibility
		";
		
		return $query;
	}

	public static function getSLABySystemsAndServices()
	{
		$query = "
			SELECT
				t1.owner,
				sv.department,
				t1.service,
				sv.name,
				/*t2.system,*/
				/*t1.contract_price,*/
				/*t1.contract_quantity,*/
				t1.contract_price*t1.contract_quantity AS contract_value,
				/*t1.calculated_price,*/
				/*t1.calculated_quantity AS service_calculated_quantity,*/
				/*t1.calculated_price*t1.calculated_quantity AS service_calculated_value,*/
				
				/*t2.total AS system_calculated_quantity,*/
				SUM(IF(t3.penality, t3.penality,0)) as penality,
				SUM(t1.calculated_price*t2.total-IF(t3.penality, t3.penality,0)) AS calculated_value
			FROM
				(
					" . self::getCalculatedUnitPricesAndQuantities() . "
				) AS t1
			LEFT JOIN
				(
					" . self::getCurrentNumberOfEquipmentsAndUsers('services') . "
				) AS t2
				ON t2.`owner`=t1.`owner` AND t2.`service`=t1.`service`
			LEFT JOIN
				(
					" . self::getAffectedSystemPenalities() . "
				) AS t3
				ON t3.`owner`=t1.`owner` AND t3.`service`=t1.`service` AND t3.`system`=t2.`system`
			LEFT JOIN
				services sv
				ON sv.`id`=t1.service
			WHERE
				t1.owner=:owner AND
				sv.department=:department
			GROUP BY
				t1.service,
				t1.owner
			ORDER BY
				t1.owner,
				t1.service
		";
		
		return $query;
	}

}