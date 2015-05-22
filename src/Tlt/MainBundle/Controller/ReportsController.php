<?php

namespace Tlt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use Tlt\MainBundle\Form\Model\JournalFilters;
use Tlt\MainBundle\Form\Type\JournalFiltersType;

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
            new JournalFiltersType($this->get('security.context')),
            $journalFilters
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
        ini_set('max_execution_time', 300);
        $journalFilters = new JournalFilters();

        $journalFilters->setStart($this->setStartDate());
        $journalFilters->setEnd($this->setEndDate());

        $form = $this->createForm(
            new JournalFiltersType($this->get('security.context')),
            $journalFilters
        );

        $form->handleRequest($request);

        return array(
            'form'      => $form->createView(),
            'systems'   => array()
        );
    }

    private function setStartDate()
    {
        return new \DateTime(date( 'd.m.Y', mktime(0,0,0, (date('m'<=6) ? 1 : 7), 1, date('Y'))));
    }

    private function setEndDate()
    {
        return new \DateTime(date( 'd.m.Y', mktime(0,0,0, (date('m'<=6) ? 6 : 12),(date('m'<=6) ? 30 : 31) , date('Y'))));
    }
}
