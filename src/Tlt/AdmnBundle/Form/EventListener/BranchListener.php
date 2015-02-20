<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

use Tlt\ProfileBundle\Entity\User;

class BranchListener implements EventSubscriberInterface
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
	
    private function addBranchForm($form, $department_id = null, $service_id = null, $branch_id = null)
    {
        $formOptions = array(
            'class'         => 'TltAdmnBundle:Branch',
            'label'         => 'Agentia/Centrul',
            'attr'          => array(
                'class' => 'branch_selector',
            )
        );

        if ($this->showAll)
        {
            $formOptions['required'] = false;
            $formOptions['empty_value'] = '-- Toate --';
        }

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
		
 
         if ($branch_id) {
             $formOptions['data'] = $branch_id;
         }
 
        $form->add('branch', 'entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor = PropertyAccess::createPropertyAccessor();

        $zone = $accessor->getValue($data, 'branch');
        $service = $accessor->getValue($data, 'service');
        $department = $accessor->getValue($data, 'department');

        $zone_id = ($zone) ? $zone->getId() : null;
        $service_id = ($service) ? $service->getId() : null;
        $department_id = ($department) ? $department->getId() : null;

        $this->addBranchForm($form, $department_id, $service_id, $zone_id);

/*        $zoneLocation	=	$accessor->getValue($data, 'zoneLocation');
		// zoneLocation is selected
		if ($zoneLocation != null)
		{
			$branch_id			=	($zoneLocation) ? $zoneLocation->getBranch()->getId() : null;
			
			$this->addBranchForm($form, null, null, $branch_id);
		// service is selected
		} else {
			$service	=	$accessor->getValue($data, 'service');

			if ($service != null)
			{
				$this->addBranchForm($form, null, $service->getId(), $zone_id);
			// department is selected
			} else {
				$department	=	$accessor->getValue($data, 'department');
				if ($department != null)
				{
					$this->addBranchForm($form, $department->getId(), 'department');
				// nothing is selected
				} else {
					$this->addBranchForm($form, null);
				}
			}
		}*/
    }
 
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
		$department_id	= array_key_exists('department', $data) ? $data['department'] : null;
        $service_id		= array_key_exists('service', $data) ? $data['service'] : null;
        $zone_id		= array_key_exists('branch', $data) ? $data['branch'] : null;
		
		$this->addBranchForm($form, $department_id, $service_id, $zone_id);
    }
}