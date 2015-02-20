<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

use Tlt\ProfileBundle\Entity\User;

class ServiceListener implements EventSubscriberInterface
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
	
    private function addServiceForm($form, $department_id = null, $service = null)
    {
		$userBranches		=	$this->user->getBranchesIds();
		$userDepartments	=	$this->user->getDepartmentsIds();
		
        $formOptions = array(
            'class'         => 'TltAdmnBundle:Service',
            'label'         => 'Serviciul',
            'attr'          => array(
                'class' => 'service_selector',
            )
        );

        if ($this->showAll)
        {
            $formOptions['required'] = false;
            $formOptions['empty_value'] = '-- Toate --';
        }

        $formOptions['query_builder'] = function (EntityRepository $repository) use ($department_id, $service, $userBranches, $userDepartments) {
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
            };

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
        $department	=	$accessor->getValue($data, 'department');

        $service_id	=	($service) ? $service->getId() : null;
        $department_id	=	($department) ? $department->getId() : null;

        $this->addServiceForm($form, $department_id, $service_id);

		/* service is selected
		if ($service != null)
		{
//			$service		= $service[0];
			$department_id	=	($service) ? $service->getDepartment()->getId() : null;
			
			$this->addServiceForm($form, $department_id, $service);
		/* department is selected
		} else {
			$department	=	$accessor->getValue($data, 'department');
			if ($department != null)
			{
//				$department		= $department[0];
    			$this->addServiceForm($form, $department->getId(), 'department');
			/* nothing is selected
			} else {
				$this->addServiceForm($form, null);
			}
		}*/
    }
	
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        $department_id = array_key_exists('department', $data) ? $data['department'] : null;
        $service_id = array_key_exists('service', $data) ? $data['service'] : null;
 
        $this->addServiceForm($form, $department_id, $service_id);
    }	
}