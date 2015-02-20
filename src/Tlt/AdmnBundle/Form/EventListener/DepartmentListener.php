<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

use Tlt\ProfileBundle\Entity\User;

class DepartmentListener implements EventSubscriberInterface
{
	private $user;
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
	
    private function addDepartmentForm($form, $department_id = null)
    {
        $formOptions = array(
            'class'         => 'TltAdmnBundle:Department',
            'label'         => 'Tip serviciu',
            'attr'          => array(
                'class' => 'department_selector',
            ),
        );

        if ($this->showAll)
        {
            $formOptions['required'] = false;
            $formOptions['empty_value'] = '-- Toate --';
        }
		
		$userDepartments	=	$this->user->getDepartmentsIds();
		
        $formOptions['query_builder'] = function (EntityRepository $repository) use ($userDepartments) {
													$qb = $repository->createQueryBuilder('department')
														->where('department.id in (:departments)')
														->setParameter('departments', $userDepartments);
													
													return $qb;
												};
		
		if ($department_id) {
            $formOptions['data'] = $department_id;
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
 
        $accessor = PropertyAccess::createPropertyAccessor();
        $department_id	=	($accessor->getValue($data, 'department')) ? $accessor->getValue($data, 'department') : null;

        $this->addDepartmentForm($form, $department_id);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $department_id = array_key_exists('department', $data) ? $data['department'] : null;
 
        $this->addDepartmentForm($form, $department_id);
    }
}