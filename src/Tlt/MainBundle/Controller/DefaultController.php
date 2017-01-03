<?php

namespace Tlt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Form\Type\ChooseType;

use Tlt\MainBundle\Model\SQL;

use Tlt\MainBundle\Form\Model\AnexaFilters;
use Tlt\MainBundle\Form\Type\AnexaFiltersType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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


        /**
         * TODO: aici trebuie intervnit dupa ce am modificat tabela tickets.
         */
        $affectedSystems = array();
//		$affectedSystems	=	$this->getDoctrine()
//										->getRepository('TltTicketBundle:TicketFix')
//										->getAffectedSystems($owner);
		
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
     * @Route("/systems_disponibility", name="main_bundle_systems_disponibility")
     * @Template("TltMainBundle:Default:sys_disp.html.twig")
     */
    public function systemsDisponibilityAction(Request $request)
    {
        $startPeriod = '2015-01-01';
        $endPeriod = '2015-06-30';

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
                                                ->getIndisponibleTime(new \DateTime($startPeriod), new \Datetime($endPeriod), $system);

				$guaranteedDisponibility	=	$this->getDoctrine()
														->getRepository('TltAdmnBundle:System')
														->getGuaranteedDisponibility($system);
											
				$disponibility	=	$this->getDoctrine()
											->getRepository('TltAdmnBundle:System')
											->getDisponibility($startPeriod, $endPeriod, $system);
				$systems[]	=	array(
									'id'			=>	$system->getId(),
									'name'			=>	$system->getName(),
									'guaranteed'	=>	$guaranteedDisponibility,
									'disponibility'	=>	$disponibility
								);
			}
//            die();
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
        if (!$this->get('security.context')->isGranted('ROLE_SLA')) {
            throw new AccessDeniedException();
        }

        $anexaFilters = new AnexaFilters();

        $anexaFilters->setYear(date('Y'));
        $form = $this->createForm(
            new AnexaFiltersType($this->get('security.context')),
            $anexaFilters
        );


        $form->handleRequest($request);

        $results = array();

        if ($form->isValid()) {
            $department = $anexaFilters->getDepartment();

            $services = $this->getDoctrine()
                    ->getRepository('TltAdmnBundle:Service')
                    ->findBy(array('department' => $department));

            if ($anexaFilters->getOwner() != null) {
                $ownersParam = $anexaFilters->getOwner()->getId();
            } else {
                $ownersParam = implode(",", $this->getUser()->getOwnersIds());
            }

            foreach ($services as $service)
            {
                if ($service->getId() < 25) {
                    $query = "
                            SELECT
                                ow.name as owner,
                                sv.name as service,
                                mp.price,
                                "
                                . ($anexaFilters->getOwner() != null ? "mq.quantity, t.total as real_quantity" : "SUM(mq.quantity) as quantity, SUM(t.total) as real_quantity") .
                                "
                            FROM
                                owners ow
                            JOIN
                                services sv
                            LEFT JOIN
                                money_price mp
                                ON mp.service=sv.id
                            LEFT JOIN
                                money_quantities mq
                                ON mq.service=sv.id AND mq.year=mp.year AND mq.owner=ow.id
                            LEFT JOIN
                                (
                                SELECT
                                    eq.owner,
                                    eq.service,
                                    SUM(eq.total) AS total
                                FROM
                                    equipments eq
                                WHERE
                                    eq.is_active=1
                                GROUP BY
                                    eq.owner,
                                    eq.service
                                ) AS t
                                ON t.owner=ow.id AND t.service=sv.id
                            WHERE
                                sv.id=:service
                                AND mp.year=:year
                                AND ow.id IN ($ownersParam)
                            GROUP BY
                                sv.id
                                "
                                . ($anexaFilters->getOwner() != null ? ", ow.id" : "") .
                                "
                            ORDER BY
                                sv.name
	                        ";

                    $em = $this->getDoctrine()->getManager();
                    $connection = $em->getConnection();
                    $statement = $connection->prepare( $query );

                    $statement->bindValue('year', $anexaFilters->getYear());
                    $statement->bindValue('service', $service->getId());

                    $statement->execute();

                    $res = $statement->fetchAll();
                    if ($res)
                        $results[$department->getName()][] = $res[0];
                } else {
                    $query = "
                            SELECT
                                ow.name as owner,
                                sv.name as service,
                                mp.price,
                                "
                                . ($anexaFilters->getOwner() != null ? "mq.quantity, m.total as real_quantity" : "SUM(mq.quantity) as quantity, SUM(m.total) as real_quantity") .
                                "
                            FROM
                                owners ow
                            JOIN
                                services sv
                            LEFT JOIN
                                money_price mp
                                ON mp.service=sv.id
                            LEFT JOIN
                                money_quantities mq
                                ON mq.service=sv.id AND mq.year=mp.year AND mq.owner=ow.id
                            LEFT JOIN
                                (
                                SELECT
                                    eq.owner,
                                    eq.service,
                                    COUNT(mps.id) as total
                                FROM
                                    mappings mps
                                LEFT JOIN
                                    equipments eq
                                    ON eq.id=mps.equipment
                                WHERE
                                    eq.is_active=1
                                GROUP BY
                                    eq.owner,
                                    eq.service
                                ) AS m
                                ON m.owner=ow.id AND m.service=sv.id
                            WHERE
                                sv.id=:service
                                AND mp.year=:year
                                AND ow.id IN ($ownersParam)
                            GROUP BY
                                sv.id
                                "
                        . ($anexaFilters->getOwner() != null ? ", ow.id" : "") .
                        "
                            ORDER BY
                                sv.name
	                        ";

                    $em = $this->getDoctrine()->getManager();
                    $connection = $em->getConnection();
                    $statement = $connection->prepare( $query );

                    $statement->bindValue('year', $anexaFilters->getYear());
                    $statement->bindValue('service', $service->getId());

                    $statement->execute();

                    $results[$department->getName()][] = $statement->fetchAll()[0];
                }
            }
        }

        return
            array(
                'form' => $form->createView(),
                'results' => $results
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


    /**
     * @Route("/email", name="email")
     * @Template()
     */
    public function emailAction(Request $request)
    {
        $mailer = $this->get('mailer');
        $message = $mailer->createMessage()
            ->setSubject('You have Completed Registration!')
            ->setFrom('send@example.com')
            ->setTo('cbradescu@teletrans.ro')
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                    'Emails/ticket_new.html.twig',
                    array('name' => 'Catalin')
                ),
                'text/html'
            )
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;
        $mailer->send($message);
    }
}