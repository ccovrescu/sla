<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

use Tlt\ProfileBundle\Entity\User;

class LocationListener implements EventSubscriberInterface
{
    private $showAll;

    public function __construct(User $user = null, $showAll = true)
    {
        $this->user = $user;
        $this->showAll = $showAll;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
	
    private function addLocationForm($form, $department_id = null, $service_id = null, $branch_id = null, $zoneLocation_id = null )
    {
        $formOptions = array(
						'class'         => 'TltAdmnBundle:ZoneLocation',
						'label'         => 'Locatia',
						'attr'          => array(
							'class' => 'location_selector',
						)
        );

        if ($this->showAll=='view')
        {
            $formOptions['required'] = false;
            $formOptions['empty_value'] = '-- Toate --';
        }

        $formOptions['query_builder']	=	function (EntityRepository $repository) use ($branch_id, $service_id, $department_id) {
												$qb = $repository->createQueryBuilder('zl')
													->innerJoin('zl.location', 'l')
													->innerJoin('zl.equipments', 'eq')
													->where('zl.branch = :branch')
													->andWhere('eq.isActive = :isActive')
													->setParameter('branch', $branch_id)
													->setParameter('isActive', true)
													->orderBy('l.name', 'ASC');
													
												if ($service_id) {
													$qb->andWhere('eq.service = :service')
														->setParameter('service', $service_id);
												} elseif ($department_id) {
													$qb->innerJoin('eq.service', 'sv')
														->andWhere('sv.department = :department')
														->setParameter('department', $department_id);
												}
								 
												return $qb;
											};

        if ($zoneLocation_id) {
            $formOptions['data'] = $zoneLocation_id;
        }

        $form->add('zoneLocation', 'entity', $formOptions);
    }
	
	public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
		
        $accessor	= PropertyAccess::createPropertyAccessor();

        $zoneLocation = $accessor->getValue($data, 'zoneLocation');
        $zone = $accessor->getValue($data, 'branch');
        $service = $accessor->getValue($data, 'service');
        $department = $accessor->getValue($data, 'department');
        $zoneLocation_id = ($zoneLocation) ? $zoneLocation->getId() : null;
        $zone_id = ($zone) ? $zone->getId() : null;
        $service_id = ($service) ? $service->getId() : null;
        $department_id = ($department) ? $department->getId() : null;


        $this->addLocationForm($form, $department_id, $service_id, $zone_id, $zoneLocation_id );
    }
	
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
		$department_id = array_key_exists('department', $data) ? $data['department'] : null;
		$service_id	= array_key_exists('service', $data) ? $data['service'] : null;
        $branch_id	= array_key_exists('branch', $data) ? $data['branch'] : null;
        $zoneLocation_id	= array_key_exists('branch', $data) ? $data['branch'] : null;
 
        $this->addLocationForm($form, $department_id, $service_id, $branch_id, $zoneLocation_id);
    }	
}