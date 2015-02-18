<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

use Tlt\AdmnBundle\Entity\Department;
use Tlt\AdmnBundle\Entity\Location;
 
class DepartmentListener implements EventSubscriberInterface
{
	private $user;
 
    public function __construct(\Tlt\ProfileBundle\Entity\User $user = null)
    {
		$this->user			=	$user;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
	
    private function addDepartmentForm($form, $department = null)
    {
        $formOptions = array(
            'class'         => 'TltAdmnBundle:Department',
			'required'		=>	false,
            'label'         => 'Tip serviciu',
            'empty_value'   => '-- Toate --',
            'attr'          => array(
                'class' => 'department_selector',
            ),
        );
		
		$userDepartments	=	$this->user->getDepartmentsIds();
		
        $formOptions['query_builder'] = function (EntityRepository $repository) use ($userDepartments) {
													$qb = $repository->createQueryBuilder('department')
														->where('department.id in (:departments)')
														->setParameter('departments', $userDepartments);
													
													return $qb;
												};
		
		if ($department) {
            $formOptions['data'] = $department;
        }
 
        $form->add('department', 'entity', $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor = PropertyAccess::getPropertyAccessor();
 
        $department	=	($accessor->getValue($data, 'department')) ? $accessor->getValue($data, 'department')[0] : null;
		
        $this->addDepartmentForm($form, $department);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
 
        $this->addDepartmentForm($form);
    }
}