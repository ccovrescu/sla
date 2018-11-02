<?php

namespace Tlt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Form\Type\ChooseType;

use Tlt\MainBundle\Model\SQL;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template("TltMainBundle:Default:index.html.twig")
     */
	public function indexAction(Request $request)
    {
		// $sql = SQL::getAffectedSystemPenalities();
		// $sql = SQL::getSLABySystemsAndServices();
		// $sql = SQL::getSystemsDisponibility();
		// $sql = SQL::getResolvedTimeOfAllServicesWithSystems();
		// $sql = SQL::getAffectedSystemsResolvedTimes();
		// $sql = SQL::getSystemsDisponibilityByService();
		// $sql = SQL::getCurrentNumberOfEquipmentsAndUsers('system');
		// $sql = SQL::getCurrentNumberOfEquipmentsAndUsers('systems');
		// $sql = SQL::getCurrentNumberOfEquipmentsAndUsers('services');
		// $sql = SQL::getCurrentNumberOfEquipmentsAndUsers('service');
		// $sql = SQL::getCalculatedUnitPricesAndQuantities();
		// $sql = SQL::getDisponibilityForAffectedSystems();
		// $sql = SQl::getSystemWithResolvedTimes();
		// $sql = SQL::x2();
		
		
		
		// $sql = str_replace(':start', "'2014-07-01 00:00:00'", $sql);
		// $sql = str_replace(':end', "'2014-12-31 23:59:59'", $sql);
		// $sql = str_replace(':year', '2014', $sql);
		// $sql = str_replace(':owner', 9, $sql);
		// $sql = str_replace(':department', 2, $sql);
		
		// $sys = 38;
		
		// $units = $this->getDoctrine()
					// ->getRepository('TltAdmnBundle:System')
					// ->getGlobalUnitsNo($sys);
		
		// $indisponibleTime = $this->getDoctrine()
					// ->getRepository('TltAdmnBundle:System')
					// ->getIndisponibleTime('2014-07-01 00:00:00', '2014-12-31 23:59:59', $sys);
		
		// $disponibility = $this->getDoctrine()
					// ->getRepository('TltAdmnBundle:System')
					// ->getDisponibility('2014-07-01 00:00:00', '2014-12-31 23:59:59', $sys);
					
					
		// $services = $this->getDoctrine()
					// ->getRepository('TltAdmnBundle:Service')
					// ->findAll();
		
		
		
		
		// $value	=	$this->getDoctrine()
							// ->getRepository('TltAdmnBundle:Service')
							// ->getServiceValue(2014, 10, 10);
		// echo '<pre>';var_dump($value); echo '</pre><br>';die();
		
		
		// $srvs = array();
		// foreach ($services as $service)
		// {
			// $systems	=	$this->getDoctrine()
							// ->getRepository('TltAdmnBundle:Service')
							// ->getServiceUnitsNo($service->getId());
							
			// $sys = array();
			// foreach($systems as $system)
				// $sys[$system['system']] = $system['units'];
			// $sys['total'] = array_sum($sys);
			// $sys['value']	=	$this->getDoctrine()
										// ->getRepository('TltAdmnBundle:Service')
										// ->getServiceValue(2014, 10, 10);
			
			// $srvs[$service->getId()] = $sys;
		// }
		// echo '<pre>';var_dump($srvs); echo '</pre><br>';die();
		
		
		
		$penalities	=	array();
		$owner	=	3;
		$year	=	2014;
		$startMoment	=	'2014-07-01 00:00:00';
		$endMoment		=	'2014-12-31 23:59:59';
		
		
		$affectedSystems	=	$this->getDoctrine()
										->getRepository('TltTicketBundle:TicketFix')
										->getAffectedSystems($owner);
		
		foreach($affectedSystems as $affectedSystem)
		{
			$service = $affectedSystem['service'];
			
			$calculatedDisponibility	=	$this->getDoctrine()
													->getRepository('TltAdmnBundle:System')
													->getDisponibility($startMoment, $endMoment, (int) $affectedSystem['system']);
			
			$guaranteedDisponibility	=	$this->getDoctrine()
													->getRepository('TltAdmnBundle:System')
													->getGuaranteedDisponibility((int) $affectedSystem['system']);
			
			if ($calculatedDisponibility < $guaranteedDisponibility)
			{
				$penalityPercent	=	round((1 - $calculatedDisponibility / $guaranteedDisponibility) * 100,2 );
				
				if ($penalityPercent > 10)	# no more than 10% !!!
					$penalityPercent	=	10;
				
				$serviceValue	=	$this->getDoctrine()
													->getRepository('TltAdmnBundle:Service')
													->getServiceValue($year, $owner, (int) $affectedSystem['service']);
				$serviceUnits	=	$this->getDoctrine()
													->getRepository('TltAdmnBundle:Service')
													->getServiceUnitsNo((int) $affectedSystem['service']);
				$serviceUnitPrice	=	round($serviceValue / $serviceUnits['total'], 2);
				
				$penalities[$affectedSystem['service']]	=	(isset($penalities[$affectedSystem['service']]) ? $penalities[$affectedSystem['service']] : 0) + $penalityPercent * $serviceUnitPrice * $serviceUnits[$affectedSystem['system']];
													
				// echo 'Affected system: <pre>';var_dump($affectedSystem); echo '</pre><br>';
				// echo 'Calculated disponibility: <pre>';var_dump($calculatedDisponibility); echo '</pre><br>';
				// echo 'Guaranteed disponibility: <pre>';var_dump($guaranteedDisponibility); echo '</pre><br>';
				// echo 'Penality percent: <pre>';var_dump($penalityPercent); echo '</pre><br>';
				// echo 'Service value: <pre>';var_dump($serviceValue); echo '</pre><br>';
				// echo 'Service units: <pre>';var_dump($serviceUnits); echo '</pre><br>';
				// echo 'System units: <pre>';var_dump($serviceUnits[$affectedSystem['system']]); echo '</pre><br>';
				// echo 'Service unit price: <pre>';var_dump($serviceUnitPrice); echo '</pre><br>';
				// echo 'Penality: <pre>';var_dump($penalities); echo '</pre><br>';
				// echo '<br> ########################## <br>';
			}
		} // affectedSystems
			// die();
		
		
		
		return
			array (
				// 'query'	=>	$sql,
				// 'system'	=> $sys,
				// 'indisponibleTime'	=> $indisponibleTime,
				// 'units'	=>	$units,
				// 'disponibility' => $disponibility,
				// 'sts'	=> array()//$servicesToSystems
			); 
	}
	
    /**
     * @Route("/sys_disp", name="systems_disponibility")
     * @Template("TltMainBundle:Default:sys_disp.html.twig")
     */
    public function systemsDisponibilityAction(Request $request)
    {
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
			new Choose(),
			array(
				'department' => array(
					'available'=>true,
					'showAll' => true
				),
			)
		);
		
		$form->handleRequest($request);	
		
		$systems = null;
		
		if ($form->isValid()) {
			$department	=	(int) $form['department']->getData();
			if ( $department != 0)
				$resultSet = $this->getDoctrine()
					->getRepository('TltAdmnBundle:System')
					->findByDepartment($department);
			else
				$resultSet = $this->getDoctrine()
					->getRepository('TltAdmnBundle:System')
					->findAll();
			
			$systems = array();
			foreach($resultSet as $system) {
				$units	=	$this->getDoctrine()
									->getRepository('TltAdmnBundle:System')
									->getGlobalUnitsNo($system);
									
				$indisponibleTime	=	$this->getDoctrine()
												->getRepository('TltAdmnBundle:System')
												->getIndisponibleTime('2014-07-01 00:00:00', '2014-12-31 23:59:59', $system);
												
				$guaranteedDisponibility	=	$this->getDoctrine()
														->getRepository('TltAdmnBundle:System')
														->getGuaranteedDisponibility($system);
											
				$disponibility	=	$this->getDoctrine()
											->getRepository('TltAdmnBundle:System')
											->getDisponibility('2014-07-01 00:00:00', '2014-12-31 23:59:59', $system);
				$systems[]	=	array(
									'id'			=>	$system->getId(),
									'name'			=>	$system->getName(),
									'guaranteed'	=>	$guaranteedDisponibility,
									'disponibility'	=>	$disponibility
								);
			}
		}
		
        return
			array(
				'form'		=> $form->createView(),
				'systems'	=> $systems
			);
    }

    /**
     * @Route("/anexa", name="anexa")
     * @Template("TltMainBundle:Default:anexa.html.twig")
     */
	public function anexaAction(Request $request)
	{
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
			new Choose(),
			array(
				'owner' => array(
					'available'=>true,
					'showAll' => true
				),
			)
		);
		
		$form->handleRequest($request);
		
		$owner = null;
		$results = array();
		
		if ($form->isValid()) {
			$owner = $this->getDoctrine()
				->getRepository('TltAdmnBundle:Owner')
				->findOneById($form['owner']->getData());

			if ($owner) {
				$departments = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Department')
					->findAll();
				
				foreach ($departments as $department)
				{
					$em = $this->getDoctrine()->getEntityManager();
					$connection = $em->getConnection();
					$statement = $connection->prepare(
						"SELECT sv.`name` AS service, mp.`price`, IF(mq.`quantity`, mq.`quantity`, '-') AS `quantity`, IF(t1.total, t1.total,'-') as real_quantity
						FROM money_quantities mq
						LEFT JOIN services sv ON sv.`id`=mq.`service`
						LEFT JOIN money_price mp ON (mp.`service`=mq.`service` AND mp.`year`=mq.`year`)
						LEFT JOIN owners ow ON ow.`id`=mq.`owner`
						LEFT JOIN (
							SELECT ss.`service`, SUM(eq.`total`) AS total FROM service_to_systems ss
							LEFT JOIN mappings mp ON mp.`system`=ss.`system`
							LEFT JOIN equipments eq ON (eq.id=mp.`equipment` AND eq.`service`=ss.`service`)
							WHERE eq.`owner`=:owner
							GROUP BY ss.`service`
							ORDER BY ss.`service`
						) AS t1
						ON t1.service=mq.`service`
						WHERE mq.`owner`=:owner AND sv.department=:department
						ORDER BY sv.`id`"
					);
					$statement->bindValue('owner', $owner->getId());
					$statement->bindValue('department', $department->getId());
					$statement->execute();
					$results[$department->getName()] = $statement->fetchAll();
				}
			} else {
				$departments = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Department')
					->findAll();
				
				foreach ($departments as $department)
				{
					$em = $this->getDoctrine()->getEntityManager();
					$connection = $em->getConnection();
					$statement = $connection->prepare(
						"SELECT sv.`name` AS service, mp.`price`, SUM(mq.`quantity`) AS `quantity`, IF(t1.total, t1.total,'-') as real_quantity
						FROM money_quantities mq
						LEFT JOIN services sv ON sv.`id`=mq.`service`
						LEFT JOIN money_price mp ON (mp.`service`=mq.`service` AND mp.`year`=mq.`year`)
						LEFT JOIN (
							SELECT ss.`service`, SUM(eq.`total`) AS total FROM service_to_systems ss
							LEFT JOIN mappings mp ON mp.`system`=ss.`system`
							LEFT JOIN equipments eq ON (eq.id=mp.`equipment` AND eq.`service`=ss.`service`)
							GROUP BY ss.`service`
							ORDER BY ss.`service`
						) AS t1
						ON t1.service=mq.`service`
						WHERE sv.department=:department
						GROUP BY mq.`service`
						ORDER BY sv.`id`"
					);
					$statement->bindValue('department', $department->getId());
					$statement->execute();
					$results[$department->getName()] = $statement->fetchAll();
				}
			}
		}
		
        return
			array(
				'form' => $form->createView(),
				'owner'	=> $owner,
				'results'	=> $results
			);
	}

    /**
     * @Route("/srv_disp", name="services_disponibility")
     * @Template("TltMainBundle:Default:srv_disp.html.twig")
     */
    public function servicesDisponibilityAction(Request $request)
	{
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
			new Choose(),
			array(
				'owner' => array(
					'available'=>true,
					'showAll' => true
				),
				'department' => array(
					'available'=>true,
					'showAll' => false
				)
			)
		);
		
		$form->handleRequest($request);
		
		$services = null;
		
		if ($form->isValid()) {
			$sql = new SQL();
			$query = $sql::getServicesDisponibility($form['owner']->getData(), $form['department']->getData());
			
			$em = $this->getDoctrine()->getManager();
			$connection = $em->getConnection();
			$statement = $connection->prepare($query);
				
			if ($form['owner']->getData() != 0)
				$statement->bindValue('owner', $form['owner']->getData());
			$statement->bindValue('department', $form['department']->getData());
			
			$statement->execute();
			$services = $statement->fetchAll();
		}
		
        return
			array(
				'form' => $form->createView(),
				'services' => $services,
				'min'	=>min($services)
			);
	}

    /**
     * @Route("/sla", name="sla")
     * @Template("TltMainBundle:Default:sla.html.twig")
     */
	public function slaAction(Request $request)
    {
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
			new Choose(),
			array(
				'owner' => array(
					'available'=>true,
					'showAll' => false
				),
			)
		);
		
		$form->handleRequest($request);
		
		$owner = null;
		$results = array();
		
		if ($form->isValid()) {
			$owner = $this->getDoctrine()
				->getRepository('TltAdmnBundle:Owner')
				->findOneById($form['owner']->getData());

			if ($owner) {
				$departments = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Department')
					->findAll();
				
				$sql = new SQL();
				foreach ($departments as $department)
				{
					$em = $this->getDoctrine()->getManager();
					$connection = $em->getConnection();
					$statement = $connection->prepare($sql->getSLA());
					$statement->bindValue('owner', $owner->getId());
					$statement->bindValue('department', $department->getId());
					$statement->execute();
					$results[$department->getName()] = $statement->fetchAll();
				}
			} else {
				$departments = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Department')
					->findAll();
				
				foreach ($departments as $department)
				{
					$em = $this->getDoctrine()->getEntityManager();
					$connection = $em->getConnection();
					$statement = $connection->prepare(
						"SELECT sv.`name` AS service, mp.`price`, SUM(mq.`quantity`) AS `quantity`, IF(t1.total, t1.total,'-') as real_quantity
						FROM money_quantities mq
						LEFT JOIN services sv ON sv.`id`=mq.`service`
						LEFT JOIN money_price mp ON (mp.`service`=mq.`service` AND mp.`year`=mq.`year`)
						LEFT JOIN (
							SELECT ss.`service`, SUM(eq.`total`) AS total FROM service_to_systems ss
							LEFT JOIN mappings mp ON mp.`system`=ss.`system`
							LEFT JOIN equipments eq ON (eq.id=mp.`equipment` AND eq.`service`=ss.`service`)
							GROUP BY ss.`service`
							ORDER BY ss.`service`
						) AS t1
						ON t1.service=mq.`service`
						WHERE sv.department=:department
						GROUP BY mq.`service`
						ORDER BY sv.`id`"
					);
					$statement->bindValue('department', $department->getId());
					$statement->execute();
					$results[$department->getName()] = $statement->fetchAll();
				}
			}
		}
		
        return
			array(
				'form' => $form->createView(),
				'owner'	=> $owner,
				'results'	=> $results
			);
	}
	
	
    /**
     * @Route("/sys_by_srv", name="systems_by_service")
     * @Template("TltMainBundle:Default:sys_by_srv.html.twig")
     */
    public function systemsByServiceAction(Request $request)
	{
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
			new Choose(),
			array(
				'department' => array(
					'available'=>true,
					'showAll' => false
				),
				'service' => array(
					'available'=>true,
					'showAll' => false
				)
			)
		);
		
		$form->handleRequest($request);
		
		$systems = null;
		
		if ($form->isValid()) {
			$sql = new SQL();
			$query = $sql::getSystemsDisponibilityByService();
			
			$em = $this->getDoctrine()->getManager();
			$connection = $em->getConnection();
			$statement = $connection->prepare($query);
				
			$statement->bindValue( ':service', $form['service']->getData() );
			$statement->bindValue( ':start', '2014-07-01 00:00:00' );
			$statement->bindValue( ':end', '2014-12-31 23:59:59' );
			
			$statement->execute();
			$systems = $statement->fetchAll();
		}
		
        return
			array(
				'form' => $form->createView(),
				'systems' => $systems,
			);
	}

    /**
     * @Route("/slax", name="slax")
     * @Template("TltMainBundle:Default:slax.html.twig")
     */
	public function slaxAction(Request $request)
	{
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
			new Choose(),
			array(
				'owner' => array(
					'available'=>true,
					'showAll' => false
				),
			)
		);
		
		$form->handleRequest($request);
		
		$owner = null;
		$results = array();
		
		if ($form->isValid()) {
			$penalities	=	array();
			$year	=	2014;
			$startMoment	=	'2014-07-01 00:00:00';
			$endMoment		=	'2014-12-31 23:59:59';
			
			$owner = $this->getDoctrine()
				->getRepository('TltAdmnBundle:Owner')
				->findOneById($form['owner']->getData());

			if ($owner) {
				$penalities	=	array();
				
				// Sistemele afectate.
				$affectedSystems	=	$this->getDoctrine()
												->getRepository('TltTicketBundle:TicketFix')
												->getAffectedSystems($owner->getId());
												
				foreach($affectedSystems as $affectedSystem)
				{
					$calculatedDisponibility	=	$this->getDoctrine()
															->getRepository('TltAdmnBundle:System')
															->getDisponibility($startMoment, $endMoment, (int) $affectedSystem['system']);
					
					$guaranteedDisponibility	=	$this->getDoctrine()
															->getRepository('TltAdmnBundle:System')
															->getGuaranteedDisponibility((int) $affectedSystem['system']);
					
					if ($calculatedDisponibility < $guaranteedDisponibility)
					{
						$penalityPercent	=	round((1 - $calculatedDisponibility / $guaranteedDisponibility) * 100,2 );
						
						if ($penalityPercent > 10)	# no more than 10% !!!
							$penalityPercent	=	10;
						
						$serviceValue	=	$this->getDoctrine()
															->getRepository('TltAdmnBundle:Service')
															->getServiceValue($year, $owner, (int) $affectedSystem['service']);
						$serviceUnits	=	$this->getDoctrine()
															->getRepository('TltAdmnBundle:Service')
															->getServiceUnitsNo((int) $affectedSystem['service']);
						$serviceUnitPrice	=	round($serviceValue / $serviceUnits['total'], 2);
						
						$penalities[$affectedSystem['service']]	=	(isset($penalities[$affectedSystem['service']]) ? $penalities[$affectedSystem['service']] : 0) + $penalityPercent * $serviceUnitPrice * $serviceUnits[$affectedSystem['system']];
															
						// echo 'Affected system: <pre>';var_dump($affectedSystem); echo '</pre><br>';
						// echo 'Calculated disponibility: <pre>';var_dump($calculatedDisponibility); echo '</pre><br>';
						// echo 'Guaranteed disponibility: <pre>';var_dump($guaranteedDisponibility); echo '</pre><br>';
						// echo 'Penality percent: <pre>';var_dump($penalityPercent); echo '</pre><br>';
						// echo 'Service value: <pre>';var_dump($serviceValue); echo '</pre><br>';
						// echo 'Service units: <pre>';var_dump($serviceUnits); echo '</pre><br>';
						// echo 'System units: <pre>';var_dump($serviceUnits[$affectedSystem['system']]); echo '</pre><br>';
						// echo 'Service unit price: <pre>';var_dump($serviceUnitPrice); echo '</pre><br>';
						// echo 'Penality: <pre>';var_dump($penalities); echo '</pre><br>';
						// echo '<br> ########################## <br>';
					}
				}
				
				
				$departments	=	$this->getDoctrine()
											->getRepository('TltAdmnBundle:Department')
											->findAll();
													
				foreach($departments as $department)
				{
					$results[$department->getName()]	=	array();
					
					$moneyQuantities	=	$this->getDoctrine()
												->getRepository('TltAdmnBundle:MoneyQuantity')
												->findAllByYearOwnerAndDepartment($year, $owner->getId(), $department->getId());
					
					$services	=	array();
					foreach ($moneyQuantities as $moneyQuantity)
					{
						$service	=	array();
						
						$moneyPrice	=	$this->getDoctrine()
												->getRepository('TltAdmnBundle:MoneyPrice')
												->findOneBy(
													array(
														'year' => $year,
														'service' => $moneyQuantity->getService()->getId()
													)
												);
							
						$service['quantity']	=	$moneyQuantity->getQuantity();
						$service['penality']	=	(isset($penalities[$moneyQuantity->getService()->getId()]) ? $penalities[$moneyQuantity->getService()->getId()] : 0);
						$service['price']	=	$moneyPrice->getPrice();
						
						$services[$moneyQuantity->getService()->getId()][$moneyQuantity->getService()->getName()]	=	$service;
					}
					
					$results[$department->getName()]	=	$services;
				}
			}
		}
		
        return
			array(
				'form' => $form->createView(),
				'owner'	=> $owner,
				'results'	=> $results
			);
	}
	
    /**
     * @Route("/sla_by_sys", name="sla_by_sys")
     * @Template("TltMainBundle:Default:sla_by_sys.html.twig")
     */	
	public function slaBySysAction(Request $request)
    {
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
			new Choose(),
			array(
				'owner' => array(
					'available'=>true,
					'showAll' => false
				),
			)
		);
		
		$form->handleRequest($request);
		
		$owner = null;
		$results = array();
		
		if ($form->isValid()) {
			$owner = $this->getDoctrine()
				->getRepository('TltAdmnBundle:Owner')
				->findOneById($form['owner']->getData());

			if ($owner) {
				$departments = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Department')
					->findAll();
				
				foreach ($departments as $department)
				{
					$em = $this->getDoctrine()->getManager();
					$connection = $em->getConnection();
					$statement = $connection->prepare( SQL::getSLABySystemsAndServices() );
					
					$statement->bindValue(':year', 2014);
					$statement->bindValue(':start',	'2014-07-01 00:00:00');
					$statement->bindValue(':end',	'2014-12-31 23:59:59');
					$statement->bindValue(':owner', $owner->getId());
					$statement->bindValue(':department', $department->getId());
					
					$statement->execute();
					
					$results[$department->getName()] = $statement->fetchAll();
				}
			}
		}
		
        return
			array(
				'form' => $form->createView(),
				'owner'	=> $owner,
				'results'	=> $results
			);
	}
 /**
     * @Route("/denied", name="denied")
     * @Template("TltMainBundle:Default:denied.html.twig")
     */
	public function deniedAction(Request $request)
    {
	}
}