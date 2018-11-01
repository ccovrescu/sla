<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 2/20/2015
 * Time: 11:13 AM
 */

namespace Tlt\TicketBundle\Form\EventListener;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Tlt\ProfileBundle\Entity\User;

class EquipmentListener implements EventSubscriberInterface
{
    private $user;
    private $showAll;

    public function __construct(User $user = null, $showAll = true)
    {
        $this->user	=	$user;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }

    private function addEquipmentForm($form, $department_id = null, $service_id = null, $branch_id = null, $zoneLocation_id = null, $owner_id = null, $equipment_id = null)
    {
        $userBranches		=	$this->user->getBranchesIds();
        $userDepartments	=	$this->user->getDepartmentsIds();

        $formOptions = array(
            'class'         => 'TltAdmnBundle:Equipment',
            'label'         => 'Echipamentul',
            'attr'          => array(
                'class' => 'equipment_selector',
            )
        );

        if ($this->showAll)
        {
            $formOptions['required'] = false;
            $formOptions['empty_value'] = '-- Toate --';
        }

        $formOptions['query_builder'] = function (EntityRepository $repository) use ($department_id, $service_id, $branch_id, $zoneLocation_id, $owner_id, $userBranches, $userDepartments) {
            $qb = $repository->createQueryBuilder('eq')
                ->innerJoin('eq.zoneLocation', 'zl')
                ->innerJoin('eq.service', 'sv')
                ->where('eq.isActive = :isActive')
                ->andWhere('zl.branch IN (:userBranches)')
                ->andWhere('sv.department IN (:userDepartments)')
                ->setParameter('isActive', true)
                ->setParameter('userBranches', $userBranches)
                ->setParameter('userDepartments', $userDepartments)
                ->orderby('eq.name', 'ASC');

            if ($owner_id) {
                $qb->andWhere('eq.owner = :owner')
                    ->setParameter('owner', $owner_id);
            }

            if ($zoneLocation_id) {
                $qb->andWhere('eq.zoneLocation = :zoneLocation')
                    ->setParameter('zoneLocation', $zoneLocation_id);
            } elseif ($branch_id) {
                $qb->andWhere('zl.branch = :branch')
                    ->setParameter('branch', $branch_id);
            }

            if ($service_id) {
                $qb->andWhere('eq.service = :service')
                    ->setParameter('service', $service_id);
            } elseif ($department_id) {
                $qb->andWhere('sv.department = :department')
                    ->setParameter('department', $department_id);
            }

            return $qb;
        };

        if ($equipment_id) {
            $formOptions['data'] = $equipment_id;
        }

        $form->add( 'equipment', 'entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $accessor	= PropertyAccess::createPropertyAccessor();

        $equipment =	$accessor->getValue($data, 'equipment');
        $owner =	$accessor->getValue($data, 'owner');
        $zoneLocation =	$accessor->getValue($data, 'zoneLocation');
        $zone =	$accessor->getValue($data, 'branch');
        $service =	$accessor->getValue($data, 'service');
        $department =	$accessor->getValue($data, 'department');

        $equipment_id =	($equipment) ? $equipment->getId() : null;
        $owner_id =	($owner) ? $owner->getId() : null;
        $zoneLocation_id =	($zoneLocation) ? $zoneLocation->getId() : null;
        $zone_id =	($zone) ? $zone->getId() : null;
        $service_id =	($service) ? $service->getId() : null;
        $department_id =	($department) ? $department->getId() : null;

        $this->addEquipmentForm($form, $department_id, $service_id, $zone_id, $zoneLocation_id, $owner_id, $equipment_id);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $department_id		= array_key_exists('department', $data) ? $data['department'] : null;
        $service_id			= array_key_exists('service', $data) ? $data['service'] : null;
        $branch_id			= array_key_exists('branch', $data) ? $data['branch'] : null;
        $zoneLocation_id	= array_key_exists('zoneLocation', $data) ? $data['zoneLocation'] : null;
        $owner_id       	= array_key_exists('owner', $data) ? $data['owner'] : null;
        $equipment_id      	= array_key_exists('equipment', $data) ? $data['equipment'] : null;

        $this->addEquipmentForm($form, $department_id, $service_id, $branch_id, $zoneLocation_id, $owner_id, $equipment_id);
    }
}