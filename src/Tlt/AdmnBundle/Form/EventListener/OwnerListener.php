<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

use Tlt\ProfileBundle\Entity\User;

class OwnerListener implements EventSubscriberInterface
{
	private $user;
    private $showAll;
	
    public function __construct(User $user = null, $showAll = true)
    {
		$this->user	=	$user;
        $this->showAll = $showAll;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
	
    private function addOwnerForm($form, $department_id = null, $service_id = null, $branch_id = null, $zoneLocation_id = null, $owner_id = null)
    {
		$userBranches		=	$this->user->getBranchesIds();
		$userDepartments	=	$this->user->getDepartmentsIds();
		
        $formOptions = array(
            'class'         => 'TltAdmnBundle:Owner',
            'label'         => 'Entitatea',
            'attr'          => array(
                'class' => 'owner_selector',
            )
        );

        if ($this->showAll)
        {
            $formOptions['required'] = false;
            $formOptions['empty_value'] = '-- Toate --';
        }

        $formOptions['query_builder'] = function (EntityRepository $repository) use ($department_id, $service_id, $branch_id, $zoneLocation_id, $userBranches, $userDepartments) {
                $qb = $repository->createQueryBuilder('ow')
                    ->innerJoin('ow.equipments', 'eq')
					->innerJoin('eq.zoneLocation', 'zl')
					->innerJoin('eq.service', 'sv')
					->where('eq.isActive = :isActive')
					->andWhere('zl.branch IN (:userBranches)')
					->andWhere('sv.department IN (:userDepartments)')
					->setParameter('isActive', true)
					->setParameter('userBranches', $userBranches)
					->setParameter('userDepartments', $userDepartments)
					->orderby('ow.name', 'ASC');
					
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

        if ($owner_id) {
            $formOptions['data'] = $owner_id;
        }

        $form->add( 'owner', 'entity', $formOptions);
    }
	
	public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor	= PropertyAccess::createPropertyAccessor();
		
		$owner =	$accessor->getValue($data, 'owner');
        $zoneLocation =	$accessor->getValue($data, 'zoneLocation');
        $zone =	$accessor->getValue($data, 'branch');
        $service =	$accessor->getValue($data, 'service');
        $department =	$accessor->getValue($data, 'department');

        $owner_id =	($owner) ? $owner->getId() : null;
        $zoneLocation_id =	($zoneLocation) ? $zoneLocation->getId() : null;
        $zone_id =	($zone) ? $zone->getId() : null;
        $service_id =	($service) ? $service->getId() : null;
        $department_id =	($department) ? $department->getId() : null;
 
        $this->addOwnerForm($form, $department_id, $service_id, $zone_id, $zoneLocation_id, $owner_id);
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
 
        $this->addOwnerForm($form, $department_id, $service_id, $branch_id, $zoneLocation_id, $owner_id);
    }	
}