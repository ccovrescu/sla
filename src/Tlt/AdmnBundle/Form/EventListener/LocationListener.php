<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Entity\Location;
 
class LocationListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
	
    private function addLocationForm($form, $department_id = null, $service_id = null, $branch_id = null )
    {
        $formOptions = array(
						'class'         => 'TltAdmnBundle:ZoneLocation',
						'required'		=>	false,
						'empty_value'   => '-- Toate --',
						'label'         => 'Locatia',
						'attr'          => array(
							'class' => 'location_selector',
						),
						'query_builder'	=>	function (EntityRepository $repository) use ($branch_id, $service_id, $department_id) {
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
											}
						);
 
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
 		
        $this->addLocationForm($form, null, null, null );
    }
	
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
		$department_id = array_key_exists('department', $data) ? $data['department'] : null;
		$service_id	= array_key_exists('service', $data) ? $data['service'] : null;
        $branch_id	= array_key_exists('branch', $data) ? $data['branch'] : null;
 
        $this->addLocationForm($form, $department_id, $service_id, $branch_id);
    }	
}