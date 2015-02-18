<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Entity\Service;
 
class ServiceListener implements EventSubscriberInterface
{
	private $user;
 
    public function __construct(\Tlt\ProfileBundle\Entity\User $user = null)
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
	
    private function addServiceForm($form, $department_id = null, $service = null)
    {
		$userBranches		=	$this->user->getBranchesIds();
		$userDepartments	=	$this->user->getDepartmentsIds();	
		
        $formOptions = array(
            'class'         => 'TltAdmnBundle:Service',
			'required'		=>	false,
            'empty_value'   => '-- Toate --',
            'label'         => 'Serviciul',
            'attr'          => array(
                'class' => 'service_selector',
            ),
            'query_builder' => function (EntityRepository $repository) use ($department_id, $service, $userBranches, $userDepartments) {
                $qb = $repository->createQueryBuilder('sv')
										->innerJoin('sv.equipments', 'eq')
										->innerJoin('eq.zoneLocation', 'zl')
										->where('eq.isActive = :isActive')
										->andWhere('zl.branch IN (:userBranches)')
										->andWhere('sv.department IN (:userDepartments)')
										->setParameter('isActive', true)
										->setParameter('userBranches', $userBranches)
										->setParameter('userDepartments', $userDepartments)
										->orderby('sv.name', 'ASC');
										
										// if ($department_id)
											$qb->andWhere('sv.department = :department')
												->setParameter('department', $department_id);
 
                return $qb;
            }
        );
		
        if ($service) {
            $formOptions['data'] = $service;
        }		
 
        $form->add('service', 'entity', $formOptions);
    }
	
	public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor	= PropertyAccess::createPropertyAccessor();
 
        $service	=	$accessor->getValue($data, 'service');
		/* service is selected */
		if ($service != null)
		{
			$service		= $service[0];
			$department_id	=	($service) ? $service->getDepartment()->getId() : null;
			
			$this->addServiceForm($form, $department_id, $service);
		/* department is selected */
		} else {
			$department	=	$accessor->getValue($data, 'department');
			if ($department != null)
			{
				$department		= $department[0];
			$this->addServiceForm($form, $department->getId(), 'department');
			/* nothing is selected */
			} else {
				$this->addServiceForm($form, null);
			}
		}
    }
	
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        $department_id = array_key_exists('department', $data) ? $data['department'] : null;
 
        $this->addServiceForm($form, $department_id);
    }	
}