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


class TicketCreateRepository extends EntityRepository {

    public function findTicketsByBranches($userBranches = null)
    {
       $qbs = $this->getEntityManager()->createQueryBuilder();

       $qbsDQL  =   $qbs->select($qbs->expr()->max('ta_s.insertedAt'))
                    ->from('TltTicketBundle:TicketAllocation', 'ta_s')
                    ->where('ta_s.ticketCreate=tc.id')
                    ->getDQL();

        $qbm = $this->getEntityManager()->createQueryBuilder()
            ->select('tc')
            ->from('TltTicketBundle:TicketCreate', 'tc')
            ->innerJoin('tc.ticketAllocations', 'ta')
            ->where('ta.insertedAt = (' . $qbsDQL . ')')
            ->andWhere('ta.branch IN (:userBranches)')
            ->setParameter('userBranches', $userBranches)
            ->orderBy('tc.id', 'DESC');


        try {
            return $qbm->getQuery()->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
} 