<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;
 
class AddDepartmentFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToService;
 
    public function __construct($propertyPathToService)
    {
        $this->propertyPathToService = $propertyPathToService;
    }
 
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
    }
 
    private function addDepartmentForm($form, $department = null)
    {
        $formOptions = array(
            'class'         => 'Tlt\AdmnBundle\Entity\Department',
            'mapped'        => false,
            'label'         => 'Departamentul',
            'empty_value'   => 'Alegeti o optiune',
            'attr'          => array(
                'class' => 'department_selector',
            ),
        );
 
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
 
        $service    = $accessor->getValue($data, $this->propertyPathToService);
        $department = ($service) ? $service->getDepartment() : null;
 
        $this->addDepartmentForm($form, $department);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
 
        $this->addDepartmentForm($form);
    }
}