<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tlt\AdmnBundle\Entity\AgencyCenter;
use Tlt\AdmnBundle\Entity\Equipment;
use Tlt\AdmnBundle\Entity\Location;
 
class AddAgencyCenterFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToLocation;
 
    public function __construct($propertyPathToLocation)
    {
        $this->propertyPathToLocation = $propertyPathToLocation;
    }
 
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
    }
 
    private function addAgencyCenterForm($form, $agency_center = null)
    {
        $formOptions = array(
            'class'         => 'Tlt\AdmnBundle\Entity\AgencyCenter',
            'mapped'        => false,
            'label'         => 'Agentia:',
        );
 
        if ($agency_center) {
            $formOptions['data'] = $agency_center;
        }
		
        $form->add('agency_center', 'entity', $formOptions);
    }
	
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
		
        $accessor = PropertyAccess::createPropertyAccessor();
		 
        $location    = $accessor->getValue($data, $this->propertyPathToLocation);
        $agency_center = ($location) ? $location->getAgencyCenter() : null;
 
        $this->addAgencyCenterForm($form, $agency_center);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
 
        $this->addAgencyCenterForm($form);
    }
}