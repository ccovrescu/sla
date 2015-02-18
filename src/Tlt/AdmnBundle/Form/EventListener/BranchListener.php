<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Entity\Location;
 
class BranchListener implements EventSubscriberInterface
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
	
    private function addBranchForm($form, $department_id = null, $service_id = null)
    {
        $formOptions = array(
            'class'         => 'TltAdmnBundle:Branch',
			'required'		=>	false,
            'label'         => 'Agentia/Centrul',
            'empty_value'   => '-- Toate --',
            'attr'          => array(
                'class' => 'branch_selector',
            ),
        );
		
		$userBranches		=	$this->user->getBranchesIds();
		$userDepartments	=	$this->user->getDepartmentsIds();
		
        $formOptions['query_builder'] = function (EntityRepository $repository) use ($department_id, $service_id, $userBranches, $userDepartments) {
													// filtram doar dupa echipamentele active si zonele userului.
													$qb = $repository->createQueryBuilder('br')
													
														->innerJoin('br.zoneLocations', 'zl')
														->innerJoin('zl.equipments', 'eq')
														->innerJoin('eq.service', 'sv')
														->where('eq.isActive = :isActive')
														->andWhere('br.id IN (:userBranches)')
														->andWhere('sv.department IN (:userDepartments)')
														->setParameter('isActive', true)
														->setParameter('userBranches', $userBranches)
														->setParameter('userDepartments', $userDepartments)
														->orderBy('br.name', 'ASC');
													
													if ($service_id)
													{
														$qb->andWhere('eq.service = :service')
															->setParameter('service', $service_id);
													} elseif ($department_id)
													{
														$qb->andWhere('sv.department = :department')
															->setParameter('department', $department_id);
													}
													
													return $qb;
												};
		
 
        // if ($branch) {
            // $formOptions['data'] = $branch;
        // }
 
        $form->add('branch', 'entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor = PropertyAccess::getPropertyAccessor();
		
		
        $zoneLocation	=	$accessor->getValue($data, 'zoneLocation');
		/* zoneLocation is selected */
		if ($zoneLocation != null)
		{
			$zoneLocation	= $zoneLocation[0];
			$branch			=	($zoneLocation) ? $zoneLocation->getBranch() : null;
			
			$this->addBranchForm($form, $branch);
		/* service is selected */
		} else {
			$service	=	$accessor->getValue($data, 'service');
			if ($service != null)
			{
				$service	=	$service[0];
				
				$this->addBranchForm($form, $service->getId(), 'service');
			/* department is selected */
			} else {
				$department	=	$accessor->getValue($data, 'department');
				if ($department != null)
				{
					$department	=	$department[0];
					$this->addBranchForm($form, $department->getId(), 'department');
				/* nothing is selected */
				} else {
					$this->addBranchForm($form, null);
				}
			}
		}
    }
 
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
		$department_id	= array_key_exists('department', $data) ? $data['department'] : null;
        $service_id		= array_key_exists('service', $data) ? $data['service'] : null;
		
		$this->addBranchForm($form, $department_id, $service_id);
    }
}