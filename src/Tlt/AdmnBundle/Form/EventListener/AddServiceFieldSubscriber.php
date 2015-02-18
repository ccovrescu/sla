<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityRepository;
use Tlt\AdmnBundle\Entity\Department;
use Tlt\AdmnBundle\Entity\Service;
 
class AddServiceFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToService;
 
    public function __construct($propertyPathToService)
    {
        $this->propertyPathToService = $propertyPathToService;
    }
 
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
 
    private function addServiceForm($form, $department_id)
    {
        $formOptions = array(
            'class'         => 'Tlt\AdmnBundle\Entity\Service',
            'empty_value'   => 'Alegeti o optiune',
            'label'         => 'Serviciul',
            'attr'          => array(
                'class' => 'service_selector',
            ),
            'query_builder' => function (EntityRepository $repository) use ($department_id) {
                $qb = $repository->createQueryBuilder('service')
                    ->innerJoin('service.department', 'department')
                    ->where('department.id = :department')
                    ->setParameter('department', ($department_id == null ? 1 : $department_id))
                ;
 
                return $qb;
            }
        );
 
        $form->add('service', 'entity', $formOptions);
    }
 
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor    = PropertyAccess::createPropertyAccessor();
 
        $service        = $accessor->getValue($data, $this->propertyPathToService);
        $department_id = ($service) ? $service->getDepartment()->getId() : null;
 
        $this->addServiceForm($form, $department_id);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        $department_id = array_key_exists('department', $data) ? $data['department'] : null;
 
        $this->addServiceForm($form, $department_id);
    }
}