<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tlt\AdmnBundle\Entity\AgencyCenter;
use Tlt\AdmnBundle\Entity\Location;
 
class AddLocationFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToLocation;
 
    public function __construct($propertyPathToLocation)
    {
        $this->propertyPathToLocation = $propertyPathToLocation;
    }
 
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
 
    private function addLocationForm($form, $agency_center_id)
    {
        $formOptions = array(
            'class'         => 'Tlt\AdmnBundle\Entity\Location',
            'empty_value'   => 'Toate',
            'label'         => 'Locatie',
            'attr'          => array(
                'class' => 'city_selector',
            ),
            'query_builder' => function (EntityRepository $repository) use ($agency_center_id) {
                $qb = $repository->createQueryBuilder('location')
                    ->innerJoin('location.agencyCenter', 'agency_center')
                    ->where('agency_center.id = :agency_center')
                    ->setParameter('agency_center', ($agency_center_id == null ? 1 : $agency_center_id))
                ;
 
                return $qb;
            }
        );
 
        $form->add($this->propertyPathToLocation, 'entity', $formOptions);
    }
 
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
		
        $accessor    = PropertyAccess::createPropertyAccessor();
 
        $location        = $accessor->getValue($data, $this->propertyPathToLocation);
        $agency_center_id = ($location) ? $location->getAgencyCenter()->getId() : null;
 
        $this->addLocationForm($form, $agency_center_id);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        $agency_center_id = array_key_exists('agency_center', $data) ? $data['agency_center'] : null;
 
        $this->addLocationForm($form, $agency_center_id);
    }
}