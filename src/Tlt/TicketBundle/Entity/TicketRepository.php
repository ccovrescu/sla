<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 2/19/2015
 * Time: 10:32 AM
 */

namespace Tlt\TicketBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;


class TicketRepository extends EntityRepository {
/*
    public function findTicketsByBranchesAndDepartments($userBranches = null, $userDepartments = null)
    {
        $qbs = $this->getEntityManager()->createQueryBuilder();

        $qbsDQL  =   $qbs->select($qbs->expr()->max('ta_s.insertedAt'))
            ->from('TltTicketBundle:TicketAllocation', 'ta_s')
            ->where('ta_s.ticket=t.id')
            ->getDQL();

        $qbm = $this->getEntityManager()->createQueryBuilder()
            ->select('t', 'ta')
            ->from('TltTicketBundle:Ticket', 't')
            ->innerJoin('t.ticketAllocations', 'ta')
            ->innerJoin('t.equipment', 'e')
            ->innerJoin('e.service', 'sv')
            ->where('ta.insertedAt = (' . $qbsDQL . ')')
            ->andWhere('ta.branch IN (:userBranches)')
            ->andWhere('sv.department IN (:userDepartments)')
            ->setParameter('userBranches', $userBranches)
            ->setParameter('userDepartments', $userDepartments)
            ->orderBy('t.id', 'DESC');

        try {
            return $qbm->getQuery()->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function findTicketsByNullEquipmentAndBranches($userBranches = null)
    {
        $qbs = $this->getEntityManager()->createQueryBuilder();

        $qbsDQL  =   $qbs->select($qbs->expr()->max('ta_s.insertedAt'))
            ->from('TltTicketBundle:TicketAllocation', 'ta_s')
            ->where('ta_s.ticket=t.id')
            ->getDQL();

        $qbm = $this->getEntityManager()->createQueryBuilder()
            ->select('t', 'ta')
            ->from('TltTicketBundle:Ticket', 't')
            ->innerJoin('t.ticketAllocations', 'ta')
            ->where('ta.insertedAt = (' . $qbsDQL . ')')
            ->andWhere('ta.branch IN (:userBranches)')
            ->andWhere('t.equipment is null')
            ->setParameter('userBranches', $userBranches)
            ->orderBy('t.id', 'DESC');

        try {
            return $qbm->getQuery()->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
*/


    /**
     * Intoarce tichetele care au generat indisponibilitate intr-o perioada si pentru un sistem si entitatea anume.
     * @param $systemID
     * @param $ownerID
     * @param $from
     * @param $to
     * @return array|null
     */
    public function getSlaTickets($systemID, $ownerID, $from, $to)
    {
        $qb	=	$this->getEntityManager()->createQueryBuilder();

        $qb
            ->select(array('t'))
            ->from('TltTicketBundle:Ticket', 't')
            ->leftJoin('t.equipment', 'e')
            ->leftJoin('t.ticketMapping', 'tm')
            ->leftJoin('tm.mapping', 'mp')
            ->where('t.isReal=1')
            ->andWhere('t.backupSolution=2')
            ->andWhere('tm.resolvedIn>0')
            ->andWhere('e.owner=:owner')
            ->andWhere('t.announcedAt BETWEEN :from AND :to')
            ->andWhere('t.fixedAt BETWEEN :from AND :to')
            ->andWhere('mp.system=:system')
            ->orderBy('t.id', 'DESC')
            ->setParameter('system', $systemID)
            ->setParameter('owner', $ownerID)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        try {
            return $qb->getQuery()->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}