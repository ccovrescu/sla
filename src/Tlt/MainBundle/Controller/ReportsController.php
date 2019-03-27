<?php

namespace Tlt\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Tlt\MainBundle\Form\Model\JournalFilters;
use Tlt\MainBundle\Form\Model\PamListFilters;
use Tlt\MainBundle\Form\Model\SlaFilters;
use Tlt\MainBundle\Form\Type\JournalFiltersType;
use Tlt\MainBundle\Form\Type\PamListFiltersType;
use Tlt\MainBundle\Form\Type\SlaFiltersType;
use Tlt\MainBundle\Model\TimeCalculation;

/**
 * @Route("/reports")
 */
class ReportsController extends Controller
{
    /**
     * @Route("/journal", name="reports_journal")
     * @Template()
     */
    public function journalAction(Request $request)
    {
		$journalFilters = new JournalFilters();

        $journalFilters->setStart($this->setStartDate());
        $journalFilters->setEnd($this->setEndDate());

        $form = $this->createForm(
            JournalFiltersType::class,
            $journalFilters,
            array(
                'objectManager'=>$this->get('doctrine.orm.entity_manager'),
                'securityContext'=>$this->container->get('security.token_storage'),
            )
        );

        $form->handleRequest($request);

        $subQueryDQL = $this->getDoctrine()->getManager()->createQueryBuilder();
        $subQueryDQL = $subQueryDQL->select($subQueryDQL->expr()->max('ta2.insertedAt'))
            ->from('TltTicketBundle:TicketAllocation', 'ta2')
            ->where($subQueryDQL->expr()->eq('ta2.ticket', 't.id'))
            ->getDQL();

        if ($journalFilters->getDepartment() !== null)
            $allowedDepartments = $journalFilters->getDepartment();
        else
            $allowedDepartments = $this->getUser()->getDepartmentsIds();

        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb = $qb->select('t')
            ->from('TltTicketBundle:Ticket', 't')
            ->innerJoin('t.ticketAllocations', 'ta', 'WITH', $qb->expr()->eq('ta.insertedAt', '(' . $subQueryDQL . ')') )
            ->leftJoin('t.equipment', 'e')
            ->leftJoin('e.service', 'sv');
        $qb->andWhere($qb->expr()->between('t.announcedAt', ':start', ':end'));
        $qb->setParameter('start', $journalFilters->getStart());
        $qb->setParameter('end', $journalFilters->getEnd());
        $qb->andWhere('e.owner = (:owner)');
        $qb->setParameter('owner', $journalFilters->getOwner());
        $qb->andWhere('ta.branch IN (:userBranches)');
        $qb->setParameter('userBranches', $this->getUser()->getBranchesIds());
        $qb->andWhere('sv.department IN (:userDepartments)');
        $qb->setParameter('userDepartments', $allowedDepartments);
        $qb->orderBy('t.announcedAt', 'ASC');

        return array(
            'form'      => $form->createView(),
            'tickets'   => $qb->getQuery()->getResult()
        );
    }

    /**
     * @Route("/sla", name="reports_sla")
     * @Template()
     */
    public function slaAction(Request $request) {
        $journalFilters = new SlaFilters();

        $journalFilters->setStart($this->setStartDate());
        $journalFilters->setEnd($this->setEndDate());

        $form = $this->createForm(
            SlaFiltersType::class,
            $journalFilters,
            array(
                'securityContext'=>$this->container->get('security.token_storage'),
            )

        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $systems = $this->getDoctrine()->getRepository('TltAdmnBundle:System')->SLA(
                $journalFilters->getOwner()->getId(),
                ($journalFilters->getDepartment() != null ? array($journalFilters->getDepartment()->getId()) : $this->getUser()->getDepartmentsIds()),
                $journalFilters->getStart()->format('Y-m-d'),
                $journalFilters->getEnd()->format('Y-m-d'),
                $journalFilters->getIsClosed()
            );
//            var_dump($systems);
//            die();
            $totalWorkingTimeInSemester = array();

            foreach ($systems as &$sys) {
                $system = $this->getDoctrine()->getRepository('TltAdmnBundle:System')->findOneById($sys['id']);
                $workingTime = $system->getGuaranteedValues()->first()->getWorkingTime();

                $units_no = $this->getDoctrine()->getRepository('TltAdmnBundle:System')->getGlobalUnitsNo($sys['id'], $journalFilters->getOwner()->getId(), $journalFilters->getStart()->format('Y-m-d'),
                    $journalFilters->getEnd()->format('Y-m-d'));
                $sys['units_no'] = $units_no;

                if (array_key_exists(
                    $workingTime->getId(),
                    $totalWorkingTimeInSemester
                )) {
                    $sys['total_time'] = $totalWorkingTimeInSemester[$workingTime->getId()];
                } else {
                    $time = TimeCalculation::getSystemTotalWorkingTime($workingTime, $journalFilters->getStart(), $journalFilters->getEnd());

                    $totalWorkingTimeInSemester[$workingTime->getId()] = $time;
                    $sys['total_time'] = $time;
                }
            }

        } else {
            $systems = array();
        }

        return array(
            'form'      => $form->createView(),
            'systems'   => $systems
        );
    }

    /**
     * @Route("/indisponibility", name="tlt_main_reports_indisponibility")
     * @Template()
     */
    public function indisponibilityAction(Request $request) {
	   $filters = new SlaFilters();

        $filters->setStart($this->setStartDate());
        $filters->setEnd($this->setEndDate());

        $form = $this->createForm(
            SlaFiltersType::class,
            $filters,
            array(
                'securityContext'=>$this->container->get('security.token_storage')
            )
        );
//		echo "<script>alert('claudiu INDISPONIBILITATE')</script>";
        $form->remove('owner');
        $form->remove('is_closed');
		$form->remove('all_units');

        $form->handleRequest($request);

        $owners = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:Owner')
            ->findAll();

        $returnedSystems = array();

        if ($form->isValid()) {

            if ($filters->getDepartment() == null) {
                $systems = $this->getDoctrine()
                    ->getRepository('TltAdmnBundle:System')
                    ->findByDepartmentIn($this->getUser()->getDepartmentsIds());
//                    ->findAll();
            } else {
                $systems = $this->getDoctrine()
                    ->getRepository('TltAdmnBundle:System')
                    ->findBy(
                        array(
                            'department' => $filters->getDepartment()
                        )
                    );
            }

            foreach ($systems as $system)
            {
                $currentSystemReturnedValues = $this->getDoctrine()->getRepository('TltAdmnBundle:System')->getDisponibilitiesForSystem( $system->getId(), $filters->getStart(), $filters->getEnd() );
                $currentSystemReturnedValuesFinal = $currentSystemAllValues = array();
                foreach ($currentSystemReturnedValues as $currentSystemReturnedValue)
                {
                    $currentSystemReturnedValuesFinal[$currentSystemReturnedValue['owner']] = $currentSystemReturnedValue['indisponibility'];
					// echo "<script>alert('$currentSystemReturnedValue['owner']')</script>";
                }

                foreach ($owners as $owner) {
                    if ( array_key_exists($owner->getId(), $currentSystemReturnedValuesFinal)) {
                        $currentSystemAllValues[$owner->getId()] = $currentSystemReturnedValuesFinal[$owner->getId()];
                    } else {
                        $currentSystemAllValues[$owner->getId()] = 0;
                    }
                }

                $returnedSystems[$system->getName()] = $currentSystemAllValues;
            }
        }
	
        return array(
            'form'      => $form->createView(),
            'owners'    => $owners,
            'systems'   => $returnedSystems
        );
    }

    private function setStartDate()
    {
        return new \DateTime(date( 'd.m.Y', mktime(0,0,0, (date('m')<=6 ? 1 : 7), 1, date('Y'))));
    }

    private function setEndDate()
    {
        return new \DateTime(date( 'd.m.Y', mktime(0,0,0, (date('m')<=6 ? 6 : 12),(date('m')<=6 ? 30 : 31) , date('Y'))));
    }

    /**
     * @Route("/tickets/{systemID}/{owner}/{from}/{to}", name="tlt_main_reports_tickets")
     * @Template()
     */
    public function ticketsAction(Request $request, $systemID, $owner, $from, $to) {
        $to=$to." 23:59:00";
        $tickets = $this->getDoctrine()->getRepository('TltTicketBundle:Ticket')
            ->getSlaTickets(
                $systemID,
                $owner,
                $from,
                $to
            );

        $system = $this->getDoctrine()->getRepository('TltAdmnBundle:System')->findOneById($systemID);

        return array(
            'system' => $system,
            'tickets' => $tickets
        );
    }

    /**
     * @Route("/pam_list", name="pam_list")
     * @Template()
     */
    public function pamListAction(Request $request)
    {
//        $session = $this->get('session');
        $pamListFilters = new PamListFilters();

        $form = $this->createForm(
            PamListFiltersType::class,
            $pamListFilters,
            array(
                'objectManager'=>$this->get('doctrine.orm.entity_manager'),
                'securityContext'=>$this->container->get('security.token_storage'),
            )
        );

        $form->handleRequest($request);

        $equipments = array();
        if ($form->isValid()) {
            // Data is valid so save it in session for another request
//            $session->set('pamListData', $form->getData());

//            if ($request->query->get('limit') != null)
//                $session->set('limit', $request->query->get('limit', 10));

            if ($pamListFilters->getDepartment() !== null) {
                $allowedDepartments = $pamListFilters->getDepartment();
            } else {
                $allowedDepartments = $this->getUser()->getDepartmentsIds();
            }

            $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
            $qb = $qb->select('e')
                ->from('TltAdmnBundle:Equipment', 'e')
                ->leftJoin('e.service', 's')
                ->leftJoin('e.zoneLocation', 'z')
            ;
            $qb->andWhere('e.owner = (:owner)');
            $qb->andWhere('z.branch IN (:userBranches)');
            $qb->andWhere('s.department IN (:userDepartments)');
            $qb->andWhere('e.service NOT IN (25,26)');
            $qb->andWhere('e.inPam = true');
            $qb->andWhere('e.isActive = true');
            $qb->setParameter('owner', $pamListFilters->getOwner());
// introdus la 24.10.2018
            if ($pamListFilters->getService())
                $qb->andWhere('e.service=:service')
                    ->setParameter('service', $pamListFilters->getService());
            if ($pamListFilters->getSystem())
                $qb->andWhere('e.system=:system')
                    ->setParameter('system', $pamListFilters->getSystem());
// sfarsit introdus la 24.10.2018
            $qb->setParameter('userBranches', $this->getUser()->getBranchesIds());
            $qb->setParameter('userDepartments', $allowedDepartments);
            $qb->orderBy('e.name', 'ASC');
//            echo $qb->getQuery()->getSql();

            $equipments = $qb->getQuery()->getResult();
        }

//        $paginator  = $this->get('knp_paginator');
//        $paginator->setDefaultPaginatorOptions(array('limit' => $session->get('limit', $request->query->get('limit', 10))));
//        $pagination = $paginator->paginate(
//            $equipments,
//            $request->query->get('page', 1),
//            $session->get('limit', $request->query->get('limit', 10))
//        );

        return array(
            'form'          => $form->createView(),
//            'pagination'    => $pagination
            'pagination'    => $equipments
        );
    }

    /**
     * @Route("/report1", name="report1")
     * @Template()
     */
    public function report1Action(Request $request)
    {
        $journalFilters = new JournalFilters();

        $journalFilters->setStart($this->setStartDate());
        $journalFilters->setEnd($this->setEndDate());

        $form = $this->createForm(
            JournalFiltersType::class,
            $journalFilters,
            array(
                'objectManager'=>$this->get('doctrine.orm.entity_manager'),
                'securityContext'=>$this->container->get('security.token_storage'),
            )
        );

        $form->handleRequest($request);

        $subQueryDQL = $this->getDoctrine()->getManager()->createQueryBuilder();
        $subQueryDQL = $subQueryDQL->select($subQueryDQL->expr()->max('ta2.insertedAt'))
            ->from('TltTicketBundle:TicketAllocation', 'ta2')
            ->where($subQueryDQL->expr()->eq('ta2.ticket', 't.id'))
            ->getDQL();

        if ($journalFilters->getDepartment() !== null)
            $allowedDepartments = $journalFilters->getDepartment();
        else
            $allowedDepartments = $this->getUser()->getDepartmentsIds();

        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('IDENTITY(t.transmissionType) as transmission')
            ->from('TltTicketBundle:Ticket', 't')
            ->innerJoin('t.ticketAllocations', 'ta', 'WITH', $qb->expr()->eq('ta.insertedAt', '(' . $subQueryDQL . ')') )
            ->leftJoin('t.equipment', 'e')
            ->leftJoin('e.service', 'sv');
        $qb->andWhere($qb->expr()->between('t.announcedAt', ':start', ':end'));
        $qb->setParameter('start', $journalFilters->getStart());
        $qb->setParameter('end', $journalFilters->getEnd());
        $qb->andWhere('e.owner = (:owner)');
        $qb->setParameter('owner', $journalFilters->getOwner());
        $qb->andWhere('ta.branch IN (:userBranches)');
        $qb->setParameter('userBranches', $this->getUser()->getBranchesIds());
        $qb->andWhere('sv.department IN (:userDepartments)');
        $qb->setParameter('userDepartments', $allowedDepartments);
        $qb->groupBy('t.transmissionType');
        $qb->orderBy('t.announcedAt', 'ASC');

        $qbTotal = $qb;
        $qbDone = clone $qb;

        $qbTotal->addSelect('COUNT(t.id) as total');
        $totalResults = $qbTotal->getQuery()->getResult();

        $qbDone->addSelect('COUNT(t.id) as done');
        $qbDone->andWhere('t.isClosed=1');
        $doneResults = $qbDone->getQuery()->getResult();

        $results = [];
        $arr = array_merge_recursive($totalResults, $doneResults);
        foreach ($arr as $item) {
            if ($item['transmission'] != 3) {
                $results['call_center'][array_keys($item)[1]] = (isset($results['call_center']) && isset($results['call_center'][array_keys($item)[1]]) ? $results['call_center'][array_keys($item)[1]] + $item[array_keys($item)[1]] : $item[array_keys($item)[1]]);
//                $results['call_center'][array_keys($item)[1]] = $item[array_keys($item)[1]];
            } else {
                $results['auto'][array_keys($item)[1]] = (isset($results['auto']) && isset($results['auto'][array_keys($item)[1]]) ? $results['auto'][array_keys($item)[1]] + $item[array_keys($item)[1]] : $item[array_keys($item)[1]]);
//                $results['auto'][array_keys($item)[1]] = $item[array_keys($item)[1]];
            }
        }

        return array(
            'form'      => $form->createView(),
            'results'   => $results
        );
    }

    /**
     * @Route("/report2", name="report2")
     * @Template()
     */
    public function report2Action(Request $request)
    {
        $journalFilters = new JournalFilters();

        $journalFilters->setStart($this->setStartDate());
        $journalFilters->setEnd($this->setEndDate());

        $form = $this->createForm(
            JournalFiltersType::class,
            $journalFilters,
            array(
                'objectManager'=>$this->get('doctrine.orm.entity_manager'),
                'securityContext'=>$this->container->get('security.token_storage'),
            )
        );

        $form->handleRequest($request);

        $subQueryDQL = $this->getDoctrine()->getManager()->createQueryBuilder();
        $subQueryDQL = $subQueryDQL->select($subQueryDQL->expr()->max('ta2.insertedAt'))
            ->from('TltTicketBundle:TicketAllocation', 'ta2')
            ->where($subQueryDQL->expr()->eq('ta2.ticket', 't.id'))
            ->getDQL();
/*var_dump($journalFilters->getStart(), $journalFilters->getEnd()) ;
var_dump($journalFilters->getOwner());
var_dump($this->getUser()->getBranchesIds()); */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('t.announcedAt, t.fixedAt, d.name as department')
            ->from('TltTicketBundle:Ticket', 't')
            ->innerJoin('t.ticketAllocations', 'ta', 'WITH', $qb->expr()->eq('ta.insertedAt', '(' . $subQueryDQL . ')') )
            ->leftJoin('t.equipment', 'e')
            ->leftJoin('e.service', 'sv')
            ->leftJoin('sv.department', 'd');
        $qb->andWhere($qb->expr()->between('t.announcedAt', ':start', ':end'));
        $qb->setParameter('start', $journalFilters->getStart());
        $qb->setParameter('end', $journalFilters->getEnd());
        $qb->andWhere('e.owner = (:owner)');
        $qb->setParameter('owner', $journalFilters->getOwner());
        $qb->andWhere('ta.branch IN (:userBranches)');
        $qb->setParameter('userBranches', $this->getUser()->getBranchesIds());
        $qb->andWhere('t.isClosed=1');
        $qb->orderBy('sv.department', 'ASC');


        $rows = $qb->getQuery()->getResult();
        /* foreach($rows as $row)
        {
            var_dump($row['announcedAt'],$row['fixedAt'],$row['department']);
        }*/

        $nr = 0;
        $current = '';

        $results = [];
        $final = [];
        $offset = new \DateTime('@0');

        foreach($rows as $row)
        {
            if ($row['department'] != $current) {
                if ($current != '') {
                    $avTimestamp = round($offset->getTimestamp() / $nr);
                    $results[] = [
                        'department' => $current,
                        'total' => $nr,
                        'time' => new \DateInterval('PT' . $avTimestamp .'S')
                    ];
                    $offset = new \DateTime('@0');
                }

                $current = $row['department'];
                $nr = 1;

                $interval = $row['announcedAt']->diff($row['fixedAt']);

                $offset->add($interval);
            }

            $nr++;

            $interval = $row['announcedAt']->diff($row['fixedAt']);
            $offset->add($interval);
           /* var_dump($current, $offset->getTimestamp()); */
        }

        if ($nr>0) {

            $avTimestamp = round($offset->getTimestamp() / $nr);
            $results[] = [
                'department' => $current,
                'total' => $nr,
                'time' => new \DateInterval('PT' . $avTimestamp . 'S')
            ];


            $totalTime = new \DateTime('@0');
            foreach ($results as $row) {

                $from = new \DateTime('@0');
                $to = clone $from;
                $to = $to->add($row['time']);
                $diff = $from->diff($to);

                $totalTime = $totalTime->add($row['time']);

                $final[] = [
                    'department' => $row['department'],
                    'interval' => $diff
                ];
            }

            $interval = new \DateInterval('PT' . round($totalTime->getTimestamp() / count($results)) . 'S');

            $from = new \DateTime('@0');
            $to = clone $from;
            $to = $to->add($interval);
            $diff = $from->diff($to);

            $final[] = [
                'department' => 'TOTAL',
                'interval' => $diff
            ];
          /*  foreach($final as $row)
            {
                var_dump($row['department'], $row['interval']);
            }
          */
        }
    // echo "<script>alert('claudiu ')</script>";
        return array(
            'form'      => $form->createView(),
            'results'   => $final
        );
    }
}